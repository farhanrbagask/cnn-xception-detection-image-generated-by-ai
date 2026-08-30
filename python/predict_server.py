import os
import sys
import json
import traceback
import urllib.parse
from http.server import HTTPServer, BaseHTTPRequestHandler
import numpy as np
import cv2

# Ensure stderr output is flushed immediately
sys.stderr.reconfigure(line_buffering=True) if hasattr(sys.stderr, 'reconfigure') else None
sys.stdout.reconfigure(line_buffering=True) if hasattr(sys.stdout, 'reconfigure') else None

# Add current directory to Python path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

try:
    import tensorflow as tf
    from tensorflow.keras.models import load_model
    print("TensorFlow imported successfully.", file=sys.stderr)
except ImportError as e:
    print(json.dumps({"error": f"Import error: {e}"}), file=sys.stderr)
    sys.exit(1)

MODELS = {}
model_dir = os.path.join(os.path.dirname(__file__), "model")
IMG_SIZE = (299, 299)

def load_existing_models():
    """Cari dan muat model .h5 yang tersedia pada startup"""
    for split in [70, 80, 90]:
        model_path = os.path.join(model_dir, f"xception_model_{split}.h5")
        if os.path.exists(model_path):
            try:
                print(f"Loading model for split {split} from {model_path}...", file=sys.stderr)
                MODELS[split] = load_model(model_path)
                print(f"Model {split} loaded successfully.", file=sys.stderr)
            except Exception as e:
                print(f"Error loading model {split}: {e}", file=sys.stderr)

# Muat semua model yang sudah ada
load_existing_models()

def preprocess_image(image_path):
    """Preprocessing gambar persis seperti predict.py"""
    try:
        img = cv2.imread(image_path)
        if img is None:
            raise ValueError(f"Gagal membaca gambar: {image_path}")

        original_height, original_width = img.shape[:2]
        original_size = (original_height, original_width)

        # Resize ke 299x299 (input size Xception)
        img_resized = cv2.resize(img, IMG_SIZE, interpolation=cv2.INTER_LINEAR)

        # Normalisasi ke [0, 1]
        img_normalized = img_resized / 255.0

        # Tambahkan dimensi batch [1, 299, 299, 3]
        img_array = np.expand_dims(img_normalized, axis=0)

        return img_array, original_size
    except Exception as e:
        raise ValueError(f"Error preprocessing gambar: {str(e)}")

class PredictHandler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        # Tulis log request ke stderr agar tidak bentrok dengan stdout
        print(f"[Server Log] {format % args}", file=sys.stderr)

    def do_GET(self):
        parsed_url = urllib.parse.urlparse(self.path)
        
        # Endpoint PING
        if parsed_url.path == '/ping':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"status": "ok", "loaded_models": list(MODELS.keys())}).encode('utf-8'))
            return

        # Endpoint PREDICT
        if parsed_url.path == '/predict':
            params = urllib.parse.parse_qs(parsed_url.query)
            image_path = params.get('image_path', [None])[0]
            split_ratio_str = params.get('split_ratio', [None])[0]

            if not image_path or not split_ratio_str:
                self.send_response(400)
                self.send_header('Content-Type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps({"error": "Missing image_path or split_ratio"}).encode('utf-8'))
                return

            try:
                split_ratio = int(split_ratio_str)
            except ValueError:
                self.send_response(400)
                self.send_header('Content-Type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps({"error": "Invalid split_ratio"}).encode('utf-8'))
                return

            # Cek jika model belum di-load, coba muat on-demand
            if split_ratio not in MODELS:
                model_path = os.path.join(model_dir, f"xception_model_{split_ratio}.h5")
                if os.path.exists(model_path):
                    try:
                        print(f"Loading model split {split_ratio} on-demand...", file=sys.stderr)
                        MODELS[split_ratio] = load_model(model_path)
                        print(f"Model split {split_ratio} loaded successfully on-demand.", file=sys.stderr)
                    except Exception as e:
                        self.send_response(500)
                        self.send_header('Content-Type', 'application/json')
                        self.end_headers()
                        self.wfile.write(json.dumps({"error": f"Failed to load model: {str(e)}"}).encode('utf-8'))
                        return
                else:
                    self.send_response(404)
                    self.send_header('Content-Type', 'application/json')
                    self.end_headers()
                    self.wfile.write(json.dumps({"error": f"Model file for split {split_ratio} not found at {model_path}"}).encode('utf-8'))
                    return

            # Proses prediksi
            try:
                if not os.path.exists(image_path):
                    self.send_response(404)
                    self.send_header('Content-Type', 'application/json')
                    self.end_headers()
                    self.wfile.write(json.dumps({"error": f"File gambar tidak ditemukan: {image_path}"}).encode('utf-8'))
                    return

                model = MODELS[split_ratio]
                img_array, original_size = preprocess_image(image_path)
                
                # Jalankan prediksi
                prediction_proba = model.predict(img_array, verbose=0)
                confidence_fake = float(prediction_proba[0][0])
                confidence_real = 1.0 - confidence_fake

                if confidence_fake > 0.5:
                    label = "fake"
                    confidence = confidence_fake
                else:
                    label = "real"
                    confidence = confidence_real

                result = {
                    "prediction": label,
                    "confidence": round(confidence * 100, 2),
                    "confidence_fake": round(confidence_fake * 100, 2),
                    "confidence_real": round(confidence_real * 100, 2),
                    "split_ratio": split_ratio,
                    "original_size": {
                        "width": original_size[1],
                        "height": original_size[0]
                    },
                    "processed_size": {
                        "width": IMG_SIZE[0],
                        "height": IMG_SIZE[1]
                    },
                    "model_path": os.path.join(model_dir, f"xception_model_{split_ratio}.h5")
                }

                self.send_response(200)
                self.send_header('Content-Type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps(result).encode('utf-8'))

            except Exception as e:
                self.send_response(500)
                self.send_header('Content-Type', 'application/json')
                self.end_headers()
                self.wfile.write(json.dumps({"error": str(e), "traceback": traceback.format_exc()}).encode('utf-8'))
        else:
            self.send_response(404)
            self.send_header('Content-Type', 'application/json')
            self.end_headers()
            self.wfile.write(json.dumps({"error": "Endpoint not found"}).encode('utf-8'))

def run(port=8001):
    server_address = ('127.0.0.1', port)
    httpd = HTTPServer(server_address, PredictHandler)
    print(f"Starting prediction server on http://127.0.0.1:{port}", file=sys.stderr)
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nStopping server...", file=sys.stderr)
        httpd.server_close()

if __name__ == '__main__':
    port = 8001
    if len(sys.argv) > 1:
        try:
            port = int(sys.argv[1])
        except ValueError:
            pass
    run(port)

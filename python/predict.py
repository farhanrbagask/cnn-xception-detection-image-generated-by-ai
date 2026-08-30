# Script untuk melakukan prediksi pada satu gambar menggunakan model Xception yang sudah ditraining
import os
import sys
import json
import traceback
import numpy as np
import cv2


# Tambahkan direktori saat ini ke path Python
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

try:
    import tensorflow as tf
    from tensorflow.keras.models import load_model
except ImportError as e:
    print(json.dumps({"error": f"Import error: {e}"}))
    sys.exit(1)

# Ukuran input yang diharapkan model Xception
IMG_SIZE = (299, 299)


def preprocess_image(image_path):
    """
    Melakukan preprocessing pada gambar yang akan diprediksi.
    
    Args:
        image_path: Path lengkap ke file gambar
    
    Returns:
        img_array: Array numpy gambar yang sudah dipreprocess, siap untuk prediksi
        original_size: Tuple ukuran gambar asli (height, width)
    """
    try:
        # Baca gambar menggunakan OpenCV
        img = cv2.imread(image_path)
        if img is None:
            raise ValueError(f"Gagal membaca gambar: {image_path}")

        # Simpan ukuran asli
        original_height, original_width = img.shape[:2]
        original_size = (original_height, original_width)

        # Resize ke 299x299 (input size Xception)
        img_resized = cv2.resize(img, IMG_SIZE, interpolation=cv2.INTER_LINEAR)

        # Normalisasi nilai pixel ke range [0, 1]
        img_normalized = img_resized / 255.0

        # Tambahkan dimensi batch (model memerlukan shape: [batch, height, width, channels])
        img_array = np.expand_dims(img_normalized, axis=0)

        return img_array, original_size

    except Exception as e:
        raise ValueError(f"Error preprocessing gambar: {str(e)}")


def predict_image(image_path, split_ratio):
    """
    Melakukan prediksi pada satu gambar menggunakan model yang sudah ditraining.
    
    Args:
        image_path: Path lengkap ke file gambar yang akan diprediksi
        split_ratio: Rasio split model yang akan digunakan (70, 80, atau 90)
    
    Returns:
        Dictionary berisi hasil prediksi
    """
    try:
        # Tentukan path model berdasarkan split ratio
        model_dir = os.path.join(os.path.dirname(__file__), "model")
        model_path = os.path.join(model_dir, f"xception_model_{split_ratio}.h5")

        # Validasi keberadaan file model
        if not os.path.exists(model_path):
            return {
                "error": f"Model untuk split ratio {split_ratio} tidak ditemukan. Pastikan model sudah ditraining terlebih dahulu.",
                "model_path": model_path
            }

        # Validasi keberadaan file gambar
        if not os.path.exists(image_path):
            return {"error": f"File gambar tidak ditemukan: {image_path}"}

        # Load model yang sudah ditraining
        print(f"Loading model dari: {model_path}", file=sys.stderr)
        model = load_model(model_path)
        print("Model berhasil dimuat", file=sys.stderr)

        # Preprocessing gambar
        print(f"Preprocessing gambar: {image_path}", file=sys.stderr)
        img_array, original_size = preprocess_image(image_path)
        print(f"Ukuran asli: {original_size[1]}x{original_size[0]}, Setelah resize: 299x299", file=sys.stderr)

        # Lakukan prediksi
        print("Melakukan prediksi...", file=sys.stderr)
        prediction_proba = model.predict(img_array, verbose=0)
        confidence_fake = float(prediction_proba[0][0])
        confidence_real = 1.0 - confidence_fake

        # Tentukan label berdasarkan threshold 0.5
        if confidence_fake > 0.5:
            label = "fake"
            confidence = confidence_fake
        else:
            label = "real"
            confidence = confidence_real

        print(f"Prediksi: {label} (confidence: {confidence:.4f})", file=sys.stderr)

        # Kembalikan hasil prediksi
        return {
            "prediction": label,
            "confidence": round(confidence * 100, 2),         # Dalam persentase
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
            "model_path": model_path
        }

    except Exception as e:
        error_msg = f"Error saat prediksi: {str(e)}\n{traceback.format_exc()}"
        print(error_msg, file=sys.stderr)
        return {"error": error_msg}


if __name__ == "__main__":
    """
    Entry point: dipanggil dari Laravel dengan argumen:
    python predict.py <image_path> <split_ratio>
    """
    try:
        # Validasi argumen
        if len(sys.argv) < 3:
            print(json.dumps({
                "error": "Argumen tidak lengkap. Penggunaan: python predict.py <image_path> <split_ratio>"
            }))
            sys.exit(1)

        image_path = sys.argv[1]
        split_ratio = int(sys.argv[2])

        # Validasi split ratio
        if split_ratio not in [70, 80, 90]:
            print(json.dumps({
                "error": f"Split ratio tidak valid: {split_ratio}. Gunakan 70, 80, atau 90."
            }))
            sys.exit(1)

        print(f"Memulai prediksi untuk gambar: {image_path}", file=sys.stderr)
        print(f"Menggunakan model split ratio: {split_ratio}%", file=sys.stderr)

        # Lakukan prediksi
        result = predict_image(image_path, split_ratio)

        # Output hasil dalam format JSON
        print(json.dumps(result))

    except Exception as e:
        error_msg = f"Error di main: {str(e)}\n{traceback.format_exc()}"
        print(error_msg, file=sys.stderr)
        print(json.dumps({"error": error_msg}))

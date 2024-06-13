<!DOCTYPE html>
<html>
<head>
    <title>Atur Ulang Kata Sandi</title>
    <style>
        body {
            width: 100% !important;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .card-text {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #6000C1;
            color: white !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 10px;
            display: inline-block;
        }
        .btn:hover {
            background-color: #36006d;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #888888;
            margin-top: 20px;
        }
        .footer a {
            color: #6000C1;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        @media screen and (max-width: 600px) {
            .card-title {
                font-size: 20px;
            }
            .card-text {
                font-size: 14px;
            }
            .btn {
                padding: 10px;
                font-size: 14px;
            }
            .footer {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <img src="https://i.ibb.co.com/ncMtX3X/bsa-removebg-preview.png" alt="Profile Picture" width="100">
            <h5 class="card-title" style="margin-top: -30px">Atur Ulang Kata Sandi</h5>
            <p class="card-text">Halo {{ $name }}, kamu mengirim permintaan atur ulang kata sandi</p>
            <p class="card-text">Berikut OTP anda:</p>
            <div class="card" style="width: 40%; margin: auto !important; padding: 1px">
                <h3>{{ $otp }}</h3>
            </div>
            <p><b>HATI HATI PENIPUAN</b>, pihak BSA <b>TIDAK AKAN</b> meminta kode OTP milik anda. Jika anda tidak meminta OTP untuk atur ulang kata sandi, silahkan abaikan email ini</p>
        </div>
        <div class="footer">
            <p>Butuh bantuan? <a href="#">Hubungi admin whatsapp kami</a> atau instagram <a href="#">@bsa.id</a>.</p>
            <p>&copy; 2024 BSA. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

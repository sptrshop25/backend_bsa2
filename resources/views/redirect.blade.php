<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: hsl(270, 100%, 59%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card img {
            width: 50px;
            margin-top: -25px;
        }
        .card-title {
            font-size: 24px;
            font-weight: bold;
        }
        .card-text {
            color: #777;
        }
        .btn-primary {
            background-color: hsl(270, 100%, 31%);
            border: none;
        }
        .btn-primary:hover {
            background-color: hsl(270, 100%, 59%);
        }
    </style>
</head>
<body>
    <div class="card text-center p-4">
        <div class="card-body">
            <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
            <div class="d-flex justify-content-center">
            <dotlottie-player src="https://lottie.host/f1a038e7-787a-44dc-a6e8-a906de07c454/kLYYUs9plv.json" background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay></dotlottie-player>
        </div>
            <h5 class="card-title mt-3">Selamat {{$user->user_name }}!</h5>
            <p class="card-text">Email anda berhasil diverifikasi, silahkan buka aplikasi BSA dan login kembali</p>
            <a href="#" class="btn btn-primary">OK</a>
        </div>
    </div>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

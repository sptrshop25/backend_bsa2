<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var countdown = 5;
            var countdownElement = document.getElementById('countdown');
            countdownElement.textContent = countdown;

            setInterval(function () {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    window.location.href = 'myapp://'; 
                }
            }, 1000);
        });
    </script>
</head>
<body>
    <h1>Email Verified</h1>
    <p>Redirecting in <span id="countdown">5</span> seconds...</p>
</body>
</html>

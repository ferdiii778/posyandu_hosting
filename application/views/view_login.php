<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>I LOVE YOU ❤️</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      overflow: hidden;
      font-family: 'Arial', sans-serif;
      position: relative;
    }

    /* Animasi background gradient */
    body::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
      animation: rotateGradient 10s linear infinite;
      opacity: 0.3;
    }

    @keyframes rotateGradient {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .love {
      font-size: 120px;
      font-weight: bold;
      background: linear-gradient(45deg, #ff0844, #ffb199, #ff0844);
      background-size: 200% 200%;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 0 40px rgba(255, 8, 68, 0.8);
      position: relative;
      z-index: 10;
      white-space: nowrap;
      animation: 
        moveX 4s ease-in-out infinite alternate,
        bounce 0.8s ease-in-out infinite,
        gradientShift 3s ease infinite,
        pulse 2s ease-in-out infinite;
      filter: drop-shadow(0 0 30px rgba(255, 8, 68, 0.6));
    }

    /* Gerak kiri-kanan */
    @keyframes moveX {
      0% { transform: translateX(-300px) scale(1); }
      100% { transform: translateX(300px) scale(1.1); }
    }

    /* Loncat-loncat */
    @keyframes bounce {
      0%, 100% { margin-top: 0; }
      50% { margin-top: -50px; }
    }

    /* Animasi gradient warna */
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Efek pulse/denyut */
    @keyframes pulse {
      0%, 100% { filter: drop-shadow(0 0 20px rgba(255, 8, 68, 0.6)); }
      50% { filter: drop-shadow(0 0 50px rgba(255, 8, 68, 1)); }
    }

    /* Partikel hati berhamburan */
    .heart {
      position: absolute;
      font-size: 30px;
      animation: float 6s ease-in infinite;
      opacity: 0;
      z-index: 1;
    }

    @keyframes float {
      0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 1;
      }
      90% {
        opacity: 1;
      }
      100% {
        transform: translateY(-100vh) rotate(360deg);
        opacity: 0;
      }
    }

    /* Sparkle/kilau */
    .sparkle {
      position: absolute;
      width: 3px;
      height: 3px;
      background: white;
      border-radius: 50%;
      box-shadow: 0 0 10px white;
      animation: sparkleAnim 2s ease-in-out infinite;
      z-index: 5;
    }

    @keyframes sparkleAnim {
      0%, 100% {
        opacity: 0;
        transform: scale(0);
      }
      50% {
        opacity: 1;
        transform: scale(1.5);
      }
    }

    /* Responsif untuk mobile */
    @media (max-width: 768px) {
      .love {
        font-size: 60px;
      }
      @keyframes moveX {
        0% { transform: translateX(-100px) scale(1); }
        100% { transform: translateX(100px) scale(1.1); }
      }
    }
  </style>
</head>
<body>
  <div class="love">I LOVE YOU ❤️</div>

  <script>
    // Generate partikel hati yang jatuh
    function createHeart() {
      const heart = document.createElement('div');
      heart.className = 'heart';
      heart.innerHTML = '❤️';
      heart.style.left = Math.random() * 100 + '%';
      heart.style.animationDuration = (Math.random() * 3 + 4) + 's';
      heart.style.animationDelay = Math.random() * 2 + 's';
      document.body.appendChild(heart);

      setTimeout(() => {
        heart.remove();
      }, 8000);
    }

    // Generate sparkle/kilau
    function createSparkle() {
      const sparkle = document.createElement('div');
      sparkle.className = 'sparkle';
      sparkle.style.left = Math.random() * 100 + '%';
      sparkle.style.top = Math.random() * 100 + '%';
      sparkle.style.animationDelay = Math.random() + 's';
      document.body.appendChild(sparkle);

      setTimeout(() => {
        sparkle.remove();
      }, 2000);
    }

    // Buat partikel hati setiap 800ms
    setInterval(createHeart, 800);

    // Buat sparkle setiap 300ms
    setInterval(createSparkle, 300);
  </script>
</body>
</html>

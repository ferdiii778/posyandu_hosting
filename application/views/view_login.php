<!DOCTYPE html>
<html>
<head>
  <title>I LOVE YOU</title>
  <style>
    body{
      margin:0;
      height:100vh;
      display:flex;
      justify-content:center;
      align-items:center;
      background:black;
      overflow:hidden;
      font-family: Arial, sans-serif;
    }

    .love{
      font-size:100px;
      font-weight:bold;
      color:red;
      text-shadow:0 0 20px pink;
      position:absolute;
      white-space:nowrap;

      /* gabungan animasi */
      animation: moveX 3s ease-in-out infinite alternate,
                 bounce 0.7s ease-in-out infinite;
    }

    /* gerak kiri-kanan */
    @keyframes moveX {
      0%   { transform: translateX(-250px); }
      100% { transform: translateX(250px); }
    }

    /* loncat-loncat */
    @keyframes bounce {
      0%, 100% { margin-top: 0; }
      50%      { margin-top: -30px; }
    }
  </style>
</head>
<body>
  <div class="love">I LOVE YOU ❤️</div>
</body>
</html>

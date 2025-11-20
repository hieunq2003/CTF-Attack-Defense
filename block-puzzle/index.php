<?php
// index.php – chỉ render HTML, JS sẽ gọi API để lấy/lưu điểm
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <title>Block Puzzle Game</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <div class="wrapper">
    <div class="game-panel">
      <canvas id="game" width="300" height="600"></canvas>

      <div class="info">
        <p>Điểm: <span id="score">0</span></p>
        <p>Level: <span id="level">1</span></p>
        <p>Lines: <span id="lines">0</span></p>
        <button id="btn-restart">Chơi lại</button>
      </div>
    </div>

    <div class="score-panel">
      <h1>Block Puzzle</h1>
      <p>Top điểm (tất cả level hoặc theo level hiện tại)</p>

      <div class="filter">
        <label>
          Lọc theo level hiện tại
          <input type="checkbox" id="filter-current-level" />
        </label>
      </div>

      <table id="score-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Tên</th>
            <th>Level</th>
            <th>Điểm</th>
          </tr>
        </thead>
        <tbody>
          <!-- JS sẽ fill -->
        </tbody>
      </table>

      <div class="footer-note">
        Dùng phím mũi tên ← → để di chuyển, ↑ để xoay, ↓ để rơi nhanh.
      </div>
    </div>
  </div>

  <!-- Overlay nhập tên khi game over -->
  <div class="overlay" id="overlay">
    <div class="overlay-content">
      <h2>Game Over</h2>
      <p>Điểm của bạn: <span id="final-score">0</span></p>
      <p>Level đạt được: <span id="final-level">1</span></p>
      <label>
        Tên của bạn:
        <input type="text" id="player-name" maxlength="50" />
      </label>
      <div class="overlay-buttons">
        <button id="btn-save-score">Lưu điểm</button>
        <button id="btn-overlay-restart">Chơi lại</button>
      </div>
      <p id="overlay-message"></p>
    </div>
  </div>

  <script src="assets/js/game.js"></script>
</body>
</html>

// assets/js/game.js

// --- Cấu hình game ---
const COLS = 10;
const ROWS = 20;
const BLOCK_SIZE = 30; // 10x20 -> canvas 300x600

const canvas = document.getElementById("game");
const ctx = canvas.getContext("2d");

const scoreEl = document.getElementById("score");
const levelEl = document.getElementById("level");
const linesEl = document.getElementById("lines");
const btnRestart = document.getElementById("btn-restart");

const overlay = document.getElementById("overlay");
const finalScoreEl = document.getElementById("final-score");
const finalLevelEl = document.getElementById("final-level");
const playerNameInput = document.getElementById("player-name");
const btnSaveScore = document.getElementById("btn-save-score");
const btnOverlayRestart = document.getElementById("btn-overlay-restart");
const overlayMsg = document.getElementById("overlay-message");

const scoreTableBody = document.querySelector("#score-table tbody");
const filterCurrentLevelCheckbox = document.getElementById(
  "filter-current-level"
);

// Board 2D
let board = [];
// Piece hiện tại
let currentPiece = null;
// Game state
let score = 0;
let lines = 0;
let level = 1;
let dropCounter = 0;
let dropInterval = 1000; // ms
let lastTime = 0;
let gameOver = false;

// Tetromino shapes
const SHAPES = {
  I: [
    [0, 0, 0, 0],
    [1, 1, 1, 1],
    [0, 0, 0, 0],
    [0, 0, 0, 0],
  ],
  J: [
    [1, 0, 0],
    [1, 1, 1],
    [0, 0, 0],
  ],
  L: [
    [0, 0, 1],
    [1, 1, 1],
    [0, 0, 0],
  ],
  O: [
    [1, 1],
    [1, 1],
  ],
  S: [
    [0, 1, 1],
    [1, 1, 0],
    [0, 0, 0],
  ],
  T: [
    [0, 1, 0],
    [1, 1, 1],
    [0, 0, 0],
  ],
  Z: [
    [1, 1, 0],
    [0, 1, 1],
    [0, 0, 0],
  ],
};

const COLORS = {
  I: "#00bcd4",
  J: "#3f51b5",
  L: "#ff9800",
  O: "#ffeb3b",
  S: "#4caf50",
  T: "#9c27b0",
  Z: "#f44336",
};

function createBoard() {
  board = [];
  for (let y = 0; y < ROWS; y++) {
    const row = new Array(COLS).fill(null);
    board.push(row);
  }
}

function createPiece() {
  const types = Object.keys(SHAPES);
  const randType = types[(Math.random() * types.length) | 0];
  const shape = SHAPES[randType];
  const color = COLORS[randType];

  return {
    x: ((COLS / 2) | 0) - ((shape[0].length / 2) | 0),
    y: 0,
    shape,
    color,
  };
}

function drawCell(x, y, color) {
  ctx.fillStyle = color;
  ctx.fillRect(x * BLOCK_SIZE, y * BLOCK_SIZE, BLOCK_SIZE - 1, BLOCK_SIZE - 1);
}

function drawBoard() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  // vẽ nền
  ctx.fillStyle = "#111";
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  // board
  for (let y = 0; y < ROWS; y++) {
    for (let x = 0; x < COLS; x++) {
      const cell = board[y][x];
      if (cell) {
        drawCell(x, y, cell);
      }
    }
  }

  // piece hiện tại
  if (currentPiece) {
    const { shape, color, x: px, y: py } = currentPiece;
    for (let y = 0; y < shape.length; y++) {
      for (let x = 0; x < shape[y].length; x++) {
        if (shape[y][x]) {
          drawCell(px + x, py + y, color);
        }
      }
    }
  }
}

function collide(board, piece) {
  const { shape, x: px, y: py } = piece;
  for (let y = 0; y < shape.length; y++) {
    for (let x = 0; x < shape[y].length; x++) {
      if (!shape[y][x]) continue;
      const nx = px + x;
      const ny = py + y;
      if (nx < 0 || nx >= COLS || ny >= ROWS || (ny >= 0 && board[ny][nx])) {
        return true;
      }
    }
  }
  return false;
}

function merge(board, piece) {
  const { shape, x: px, y: py, color } = piece;
  for (let y = 0; y < shape.length; y++) {
    for (let x = 0; x < shape[y].length; x++) {
      if (shape[y][x]) {
        if (py + y < 0) {
          // nằm trên đỉnh -> game over
          continue;
        }
        board[py + y][px + x] = color;
      }
    }
  }
}

function rotateMatrix(matrix) {
  const N = matrix.length;
  const result = [];
  for (let y = 0; y < N; y++) {
    result[y] = [];
    for (let x = 0; x < N; x++) {
      result[y][x] = matrix[N - 1 - x][y];
    }
  }
  return result;
}

function rotatePiece() {
  if (!currentPiece) return;
  const cloned = JSON.parse(JSON.stringify(currentPiece));
  cloned.shape = rotateMatrix(cloned.shape);
  if (!collide(board, cloned)) {
    currentPiece.shape = cloned.shape;
  }
}

function drop() {
  if (!currentPiece) return;
  currentPiece.y++;
  if (collide(board, currentPiece)) {
    currentPiece.y--;
    lockPiece();
    sweepLines();
    spawnPiece();
  }
}

function hardDrop() {
  if (!currentPiece) return;
  while (!collide(board, currentPiece)) {
    currentPiece.y++;
  }
  currentPiece.y--;
  lockPiece();
  sweepLines();
  spawnPiece();
}

function lockPiece() {
  merge(board, currentPiece);
}

function sweepLines() {
  let linesCleared = 0;
  outer: for (let y = ROWS - 1; y >= 0; y--) {
    for (let x = 0; x < COLS; x++) {
      if (!board[y][x]) {
        continue outer;
      }
    }
    // full row
    const row = board.splice(y, 1)[0].fill(null);
    board.unshift(row);
    linesCleared++;
    y++; // check same line index again sau khi unshift
  }

  if (linesCleared > 0) {
    lines += linesCleared;
    // công thức điểm đơn giản
    score += linesCleared * 100 * linesCleared;
    // level tăng mỗi 10 lines
    const newLevel = 1 + Math.floor(lines / 10);
    if (newLevel !== level) {
      level = newLevel;
      // tốc độ rơi nhanh hơn
      dropInterval = Math.max(200, 1000 - (level - 1) * 80);
    }
    updateHUD();
  }
}

function spawnPiece() {
  currentPiece = createPiece();
  if (collide(board, currentPiece)) {
    // game over
    gameOver = true;
    showGameOver();
  }
}

function movePiece(dir) {
  if (!currentPiece) return;
  currentPiece.x += dir;
  if (collide(board, currentPiece)) {
    currentPiece.x -= dir;
  }
}

function update(time = 0) {
  if (gameOver) {
    drawBoard();
    return;
  }

  const deltaTime = time - lastTime;
  lastTime = time;
  dropCounter += deltaTime;

  if (dropCounter > dropInterval) {
    drop();
    dropCounter = 0;
  }

  drawBoard();
  requestAnimationFrame(update);
}

function updateHUD() {
  scoreEl.textContent = score;
  levelEl.textContent = level;
  linesEl.textContent = lines;
}

// --- Game control ---
function newGame() {
  createBoard();
  score = 0;
  lines = 0;
  level = 1;
  dropInterval = 1000;
  gameOver = false;
  updateHUD();
  spawnPiece();
  lastTime = 0;
  dropCounter = 0;
  hideOverlay();
  requestAnimationFrame(update);
  loadScores(); // load lại bảng điểm
}

btnRestart.addEventListener("click", newGame);

// --- Input ---
document.addEventListener("keydown", (e) => {
  if (gameOver) return;
  switch (e.code) {
    case "ArrowLeft":
      e.preventDefault();
      movePiece(-1);
      break;
    case "ArrowRight":
      e.preventDefault();
      movePiece(1);
      break;
    case "ArrowUp":
      e.preventDefault();
      rotatePiece();
      break;
    case "ArrowDown":
      e.preventDefault();
      drop();
      dropCounter = 0;
      break;
    case "Space":
      e.preventDefault();
      hardDrop();
      dropCounter = 0;
      break;
    default:
      break;
  }
});

// --- Overlay game over + lưu điểm ---
function showOverlay() {
  overlay.classList.add("active");
}

function hideOverlay() {
  overlay.classList.remove("active");
  overlayMsg.textContent = "";
  playerNameInput.value = "";
}

function showGameOver() {
  finalScoreEl.textContent = score;
  finalLevelEl.textContent = level;
  showOverlay();
}

btnOverlayRestart.addEventListener("click", () => {
  newGame();
});

btnSaveScore.addEventListener("click", async () => {
  const name = playerNameInput.value.trim();
  if (!name) {
    overlayMsg.textContent = "Vui lòng nhập tên trước khi lưu điểm.";
    return;
  }
  try {
    overlayMsg.textContent = "Đang lưu...";
    const res = await fetch("save_score.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        name,
        score,
        level,
      }),
    });

    const data = await res.json();
    if (!data.ok) {
      overlayMsg.textContent = "Lưu điểm thất bại.";
      return;
    }

    overlayMsg.textContent = "Đã lưu điểm!";
    await loadScores();
  } catch (err) {
    console.error(err);
    overlayMsg.textContent = "Có lỗi khi kết nối server.";
  }
});

// --- Bảng xếp hạng ---
async function loadScores() {
  try {
    let url = "get_scores.php";
    if (filterCurrentLevelCheckbox.checked) {
      url += "?level=" + encodeURIComponent(level);
    }
    const res = await fetch(url);
    const data = await res.json();
    if (!data.ok) return;

    const rows = data.data;
    scoreTableBody.innerHTML = "";
    rows.forEach((row, idx) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${escapeHtml(row.name)}</td>
        <td>${row.level}</td>
        <td>${row.score}</td>
      `;
      scoreTableBody.appendChild(tr);
    });
  } catch (err) {
    console.error(err);
  }
}

filterCurrentLevelCheckbox.addEventListener("change", () => {
  loadScores();
});

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// --- Khởi động ---
newGame();

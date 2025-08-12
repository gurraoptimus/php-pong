<?php
// Simple Pong Game using PHP and HTML5 Canvas

// No PHP logic needed for game, just serve the HTML/JS
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="10;url=../index.php">
    <title>Pong Game in PHP</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1db954 0%, #191414 100%);
            font-family: 'Circular', 'Arial', sans-serif;
            position: relative;
        }
        .pong-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        canvas {
            background: #191414;
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(30,215,96,0.2), 0 1.5px 8px 0 rgba(0,0,0,0.3);
            display: block;
            margin: auto;
            border: 2px solid #1db954;
        }
        /* Pause menu styles */
        #pauseMenu {
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            background: rgba(25, 20, 20, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            flex-direction: column;
            display: none;
        }
        #pauseMenu h2 {
            color: #1db954;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        #pauseMenu p {
            color: #fff;
        }
        #pauseMenu button {
            font-size: 1.2rem;
            padding: 0.5rem 2rem;
            border-radius: 8px;
            border: none;
            background: #1db954;
            color: #fff;
            font-weight: bold;
            margin-top: 1rem;
            transition: background 0.2s;
        }
        #pauseMenu button:hover {
            background: #17a74a;
        }
        /* Pause button styles */
        #pauseBtn {
            position: absolute;
            top: 24px;
            right: 32px;
            z-index: 1100;
            font-size: 1.2rem;
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            border: none;
            background: #ffc107;
            color: #191414;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: background 0.2s;
        }
        #pauseBtn:hover {
            background: #e0a800;
        }
        @media (max-width: 700px) {
            canvas {
                width: 98vw !important;
                height: 60vw !important;
                max-width: 100%;
                max-height: 70vw;
            }
            .pong-container h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="pong-container container position-relative">
    <h1 class="text-center text-light mb-4">Pong Game in PHP</h1>
    <!-- Pause Button -->
    <button id="pauseBtn" type="button">Pause</button>
    <!-- Audio elements for sounds -->
    <audio id="hitSound" src="https://cdn.jsdelivr.net/gh/terkelg/awesome-creative-coding-assets@main/audio/pong/hit.wav"></audio>
    <audio id="scoreSound" src="https://cdn.jsdelivr.net/gh/terkelg/awesome-creative-coding-assets@main/audio/pong/score.wav"></audio>
    <canvas id="pong" width="600" height="400"></canvas>
    <div class="mt-3 text-center">
        <span class="badge bg-success">Left: W/S</span>
        <span class="badge bg-primary">Right: ↑/↓</span>
        <span class="badge bg-warning text-dark ms-2">Pause: P</span>
    </div>
    <!-- Pause Menu Overlay -->
    <div id="pauseMenu">
        <h2>Game Paused</h2>
        <p>Press <b>P</b> to resume</p>
        <button id="resumeBtn">Resume</button>
    </div>
</div>
<!-- Bootstrap JS (optional, for components) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ... (JS code remains unchanged)
const canvas = document.getElementById('pong');
const ctx = canvas.getContext('2d');

const hitSound = document.getElementById('hitSound');
const scoreSound = document.getElementById('scoreSound');

const paddleWidth = 10, paddleHeight = 80;
const ballSize = 10;

let leftPaddle = { x: 10, y: canvas.height/2 - paddleHeight/2, dy: 0 };
let rightPaddle = { x: canvas.width - 20, y: canvas.height/2 - paddleHeight/2, dy: 0 };
let ball = { x: canvas.width/2, y: canvas.height/2, dx: 4, dy: 4 };

let leftScore = 0, rightScore = 0;

let paused = false;
const pauseMenu = document.getElementById('pauseMenu');
const resumeBtn = document.getElementById('resumeBtn');
const pauseBtn = document.getElementById('pauseBtn');

function drawRect(x, y, w, h, color) {
    ctx.fillStyle = color;
    ctx.fillRect(x, y, w, h);
}

function drawCircle(x, y, r, color) {
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.arc(x, y, r, 0, Math.PI*2, false);
    ctx.closePath();
    ctx.fill();
}

function drawText(text, x, y, color) {
    ctx.fillStyle = color;
    ctx.font = "bold 32px Arial";
    ctx.shadowColor = "#1db954";
    ctx.shadowBlur = 10;
    ctx.fillText(text, x, y);
    ctx.shadowBlur = 0;
}

function resetBall() {
    ball.x = canvas.width/2;
    ball.y = canvas.height/2;
    ball.dx = -ball.dx;
    ball.dy = 4 * (Math.random() > 0.5 ? 1 : -1);
}

function playSound(audio) {
    audio.currentTime = 0;
    audio.play();
}

function update() {
    // Move paddles
    leftPaddle.y += leftPaddle.dy;
    rightPaddle.y += rightPaddle.dy;

    // Prevent paddles from going out of bounds
    leftPaddle.y = Math.max(0, Math.min(canvas.height - paddleHeight, leftPaddle.y));
    rightPaddle.y = Math.max(0, Math.min(canvas.height - paddleHeight, rightPaddle.y));

    // Move ball
    ball.x += ball.dx;
    ball.y += ball.dy;

    // Top/bottom collision
    if (ball.y < 0 || ball.y > canvas.height - ballSize) ball.dy = -ball.dy;

    // Left paddle collision
    if (ball.x < leftPaddle.x + paddleWidth &&
        ball.y + ballSize > leftPaddle.y &&
        ball.y < leftPaddle.y + paddleHeight) {
        ball.dx = -ball.dx;
        ball.x = leftPaddle.x + paddleWidth;
        playSound(hitSound);
    }

    // Right paddle collision
    if (ball.x + ballSize > rightPaddle.x &&
        ball.y + ballSize > rightPaddle.y &&
        ball.y < rightPaddle.y + paddleHeight) {
        ball.dx = -ball.dx;
        ball.x = rightPaddle.x - ballSize;
        playSound(hitSound);
    }

    // Score
    if (ball.x < 0) {
        rightScore++;
        playSound(scoreSound);
        resetBall();
    }
    if (ball.x > canvas.width) {
        leftScore++;
        playSound(scoreSound);
        resetBall();
    }
}

function draw() {
    // Draw background first
    drawRect(0, 0, canvas.width, canvas.height, "rgba(25,20,20,0.85)");
    // Spotify green center line
    drawRect(canvas.width/2 - 2, 0, 4, canvas.height, "#1db954");
    drawRect(leftPaddle.x, leftPaddle.y, paddleWidth, paddleHeight, "#1db954");
    drawRect(rightPaddle.x, rightPaddle.y, paddleWidth, paddleHeight, "#1db954");
    drawCircle(ball.x, ball.y, ballSize, "#fff");
    drawText(leftScore, canvas.width/4, 50, "#fff");
    drawText(rightScore, 3*canvas.width/4, 50, "#fff");
}

function game() {
    if (!paused) {
        update();
        draw();
    }
    requestAnimationFrame(game);
}

// Keyboard controls
document.addEventListener('keydown', function(e) {
    if (e.key === 'w') leftPaddle.dy = -6;
    if (e.key === 's') leftPaddle.dy = 6;
    if (e.key === 'ArrowUp') rightPaddle.dy = -6;
    if (e.key === 'ArrowDown') rightPaddle.dy = 6;
    if ((e.key === 'p' || e.key === 'P')) {
        if (!paused) {
            paused = true;
            pauseMenu.style.display = 'flex';
        } else {
            paused = false;
            pauseMenu.style.display = 'none';
        }
    }
});
document.addEventListener('keyup', function(e) {
    if (e.key === 'w' || e.key === 's') leftPaddle.dy = 0;
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') rightPaddle.dy = 0;
});

// Pause button
pauseBtn.addEventListener('click', function() {
    if (!paused) {
        paused = true;
        pauseMenu.style.display = 'flex';
    }
});

// Resume button
resumeBtn.addEventListener('click', function() {
    paused = false;
    pauseMenu.style.display = 'none';
});

game();
</script>
</body>
</html>
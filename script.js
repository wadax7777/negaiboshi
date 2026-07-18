/* 星生成 */
const stars = document.getElementById("stars");
if (stars) {
  for (let i = 0; i < 140; i++) {
    const s = document.createElement("div");
    s.className = "star";
    s.style.left = Math.random() * 100 + "%";
    s.style.top = Math.random() * 100 + "%";
    s.style.animationDelay = Math.random() * 5 + "s";
    s.style.opacity = Math.random();
    stars.appendChild(s);
  }
}

/* メッセージ変更 */
function changeMessage(type) {
  const msg = document.getElementById("message");
  if (!msg) return;

  const messages = {
    rain: "雨音は、星の子守唄。<br>静かな夜を、ゆっくり感じてください。",
    wind: "風は、遠い空から届く手紙。<br>今夜は少しだけ深呼吸を。",
    star: "星たちは今日も、あなたを見守っています。<br>願いは、ちゃんと空へ届きます。"
  };

  if (messages[type]) msg.innerHTML = messages[type];
}

/* 今日の空：表示画像 */
const skyImages = [
  "image/fukuoka00.jpg",
  "image/fukuoka01.jpg",
  "image/fukuoka02.jpg",
  "image/fukuoka03.jpg",
  "image/fukuoka04.jpg",
  "image/fukuoka05.jpg",
  "image/fukuoka06.jpg",
  "image/fukuoka07.jpg",
  "image/fukuoka08.jpg",
  "image/fukuoka09.jpg",
  "image/fukuoka10.jpg"
];

let currentSkyImage = -1;

function updateLastTime() {
  const target = document.getElementById("lastUpdate");
  if (!target) return;
  const now = new Date();
  const hh = String(now.getHours()).padStart(2, "0");
  const mm = String(now.getMinutes()).padStart(2, "0");
  target.textContent = hh + ":" + mm;
}

function refreshSkyImage() {
  const image = document.getElementById("skyLiveImage");
  if (!image || skyImages.length === 0) return;

  let next;
  do {
    next = Math.floor(Math.random() * skyImages.length);
  } while (skyImages.length > 1 && next === currentSkyImage);
  currentSkyImage = next;

  image.style.opacity = "0";
  const nextSrc = skyImages[next] + "?t=" + Date.now();
  const preloader = new Image();

  preloader.onload = function () {
    image.src = nextSrc;
    image.style.opacity = "1";
  };

  preloader.onerror = function () {
    image.style.opacity = "1";
  };

  preloader.src = nextSrc;
  updateLastTime();
}

updateLastTime();
refreshSkyImage();
setInterval(refreshSkyImage, 1800000); // 30分ごと

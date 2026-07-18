"use strict";

const stars = document.getElementById("stars");

if (stars) {
  for (let i = 0; i < 150; i++) {
    const star = document.createElement("span");
    star.className = "star";
    star.style.left = `${Math.random() * 100}%`;
    star.style.top = `${Math.random() * 100}%`;
    star.style.opacity = String(Math.random() * 0.8 + 0.2);
    star.style.animationDelay = `${Math.random() * 5}s`;
    star.style.animationDuration = `${Math.random() * 4 + 3}s`;
    stars.appendChild(star);
  }
}

const frame = document.getElementById("liveFrame");
const loading = document.getElementById("videoLoading");
const title = document.getElementById("viewerTitle");
const place = document.getElementById("viewerPlace");
const description = document.getElementById("viewerDescription");
const openYoutube = document.getElementById("openYoutube");
const reloadButton = document.getElementById("reloadVideo");
const fullscreenButton = document.getElementById("fullscreenVideo");
const cards = [...document.querySelectorAll(".liveCard")];

let currentVideoId = "KZsB8vpf0yg";

function embedUrl(videoId) {
  return `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoId)}?rel=0`;
}

function showLoading() {
  loading?.classList.remove("hidden");
}

function hideLoading() {
  loading?.classList.add("hidden");
}

function selectCamera(card) {
  const videoId = card.dataset.videoId || "";
  if (!videoId) return;

  currentVideoId = videoId;

  cards.forEach(item => item.classList.remove("active"));
  card.classList.add("active");

  if (title) title.textContent = card.dataset.title || "日本のライブカメラ";
  if (place) place.textContent = `📍 ${card.dataset.place || "日本"}`;
  if (description) {
    description.textContent =
      card.dataset.description || "日本各地のライブ映像です。";
  }

  if (openYoutube) {
    openYoutube.href =
      card.dataset.url || `https://www.youtube.com/watch?v=${videoId}`;
  }

  if (frame) {
    showLoading();
    frame.title = `${card.dataset.title || "日本"} ライブカメラ`;
    frame.src = embedUrl(videoId);
  }

  document.querySelector(".viewerCard")?.scrollIntoView({
    behavior: "smooth",
    block: "start"
  });
}

cards.forEach(card => {
  card.addEventListener("click", () => selectCamera(card));
});

frame?.addEventListener("load", () => {
  window.setTimeout(hideLoading, 350);
});

reloadButton?.addEventListener("click", () => {
  if (!frame) return;
  showLoading();
  frame.src = embedUrl(currentVideoId);
});

fullscreenButton?.addEventListener("click", async () => {
  const shell = document.querySelector(".videoShell");
  if (!shell) return;

  try {
    if (document.fullscreenElement) {
      await document.exitFullscreen();
    } else {
      await shell.requestFullscreen();
    }
  } catch (error) {
    console.warn("全画面表示を開始できませんでした。", error);
  }
});

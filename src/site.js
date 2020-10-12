const { initNoise, setupLineSVG, animateLine } = require("./initLine");
const { initTabs } = require("./initTabs");

document.addEventListener("DOMContentLoaded", () => {
  initNoise();
  setupLineSVG();
  animateLine();
  initTabs();
  document.body.style.setProperty("--y", 0);
});
window.addEventListener("resize", (e) => {
  setupLineSVG();
});
window.addEventListener("scroll", (e) => {
  document.body.style.setProperty("--y", window.scrollY);
});

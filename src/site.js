const { initLine } = require("./initLine");
const { initTabs } = require("./initTabs");

document.addEventListener("DOMContentLoaded", () => {
  initLine();
  initTabs();
  document.body.style.setProperty("--y", 0);
});
window.addEventListener("scroll", (e) => {
  document.body.style.setProperty("--y", window.scrollY);
});
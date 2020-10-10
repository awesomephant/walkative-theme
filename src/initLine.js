const noise = require("simplenoise");

const config = {
  pointCount: 150,
  debug: true,
  noiseAmplitude: 200,
  noiseFrequency: 0.0008,
  speed: 0.00023,
  slope: 0.3,
  stackCount: 4,
  lineWidth: window.innerHeight * .22,
};

let points = [];
let path, h, w;
let offset = 0;
let currentSpeed = config.speed;
let targetSpeed = config.speed;

function generatePathString(points) {
  let s = `M${points[0].currentX} ${points[0].currentY} `;
  for (let i = 1; i < points.length; i++) {
    s += `L${points[i].currentX} ${points[i].currentY} `;
  }
  s += `L${points[points.length - 1].currentX} ${
    points[points.length - 1].currentY + config.lineWidth
  } `;

  // we loop backwards through the points array
  for (let i = points.length - 1; i > -1; i--) {
    s += `L${points[i].currentX} ${points[i].currentY + config.lineWidth}`;
  }
  return s;
}

function sq(a) {
  return a * a;
}

function animateLine() {
  window.requestAnimationFrame(animateLine);
  currentSpeed += (targetSpeed - currentSpeed) * 0.1;
  offset += currentSpeed;

  for (let i = 0; i < points.length - 1; i++) {
    points[i].currentY =
      h / 2 +
      noise.simplex2(points[i].currentX * config.noiseFrequency + offset, 3) *
        config.noiseAmplitude -
      points[i].currentX * config.slope +
      (noise.simplex2(points[i].currentX * 0.005 + offset, 3) * 15 + 100);

    let adjacent = points[i + 1].currentX - points[i].currentX;
    let opposite = points[i + 1].currentY - points[i].currentY;
    let d = Math.sqrt(sq(adjacent) + sq(opposite));
    let angle = (180 * Math.asin(opposite / d)) / Math.PI;
    points[i].currentSlope = angle;
  }

  path.setAttribute("d", generatePathString(points));
}

function initLine(cb) {
  let svgEl = document.querySelector(".line");
  w = window.innerWidth;
  h = window.innerHeight;
  svgEl.setAttribute("width", w);
  svgEl.setAttribute("height", h);

  noise.seed(Math.random());

  // Init points array
  for (let i = 0; i < config.pointCount + 2; i++) {
    let x = ((w + 200) / config.pointCount) * i - 100;
    points.push({
      currentX: x,
      currentY: h / 2,
    });
  }

  path = document.createElementNS("http://www.w3.org/2000/svg", "path");
  path.setAttribute("d", generatePathString(points));
  path.setAttribute("class", "line");
  
  let defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
  let cp = document.createElementNS("http://www.w3.org/2000/svg", "clipPath");

  cp.setAttribute("id", "cp");
  cp.insertAdjacentElement("afterbegin", path);
  defs.insertAdjacentElement("afterbegin", cp);
  
  svgEl.insertAdjacentElement("afterbegin", defs);
  animateLine();
}

export { initLine, animateLine };

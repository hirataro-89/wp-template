import '../style/style.scss';
export * from "@js/viewport";
import imgSrc from "/images/js.png";

// jsから画像を読み込むサンプル
const canvas = document.querySelector("#canvas");
const context = canvas.getContext("2d");
const image = new Image();
image.width = 300;
image.height = 300;
image.src = imgSrc;
image.onload = function() {
  context.drawImage(image, 0, 0, 300, 300);
};
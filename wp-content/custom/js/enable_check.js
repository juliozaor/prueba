
function desactivar_tyc() {
  document.getElementById("submit-button").disabled = true;
  document.getElementById("submit-button").style.visibility = "hidden";
}

function activar_tyc() {
  document.getElementById("submit-button").disabled = false;
  document.getElementById("submit-button").style.visibility = "visible";
  document.getElementById("PoliticaTratamientoDatos").style.visibility = "hidden";
}


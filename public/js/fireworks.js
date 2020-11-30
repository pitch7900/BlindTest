https://codepen.io/nuton0413/pen/QWNgzKO
var fireworks = function(target,fullscreen) {
    if (!PIXI.utils.isWebGLSupported()) return;
  
    const fireworks = new FIREWORKS({
      full_screen: fullscreen,
      target_node: target,
      amount: 5
    });
    
    const is_mobile = 'ontouchend' in document;
    if (!is_mobile) {
      fireworks.start_burst();
      return;
    }
    
    const tap_to_start = document.createElement("div");
    tap_to_start.innerText = "TAP TO START";
    tap_to_start.classList.add("start");
    document.body.appendChild(tap_to_start); 
    
    window.addEventListener("touchend", function init() {
      window.removeEventListener("touchend", init);
      document.body.removeChild(tap_to_start);
      
      fireworks.start_burst();
    }, false);
  };
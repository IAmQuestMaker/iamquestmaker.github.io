 var x = 0;
 
 function click1() {
    let f1 = document.getElementsByName("field");
    let r = document.getElementById("result");
    let s = document.getElementsByName("select");
    console.log(s[0].value);
    let m = f1[0].value.match(/[A-z]/g);
    if (m!==null) {x=1;}
    r.innerHTML = f1[0].value * s[0].value;
    return false;
  }

  function onClick(event){
    event.preventDefault();
    if (x==1){
        alert("Entered wrong symbols!");
        x = 0;
    }
  }

  window.addEventListener('DOMContentLoaded', function (event) {
    console.log("DOM fully loaded and parsed");
    let b = document.getElementById("button1");
    b.addEventListener("click", onClick);
  });
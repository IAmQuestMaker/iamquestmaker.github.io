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

  window.onload = function () { 
    let form = document.getElementById("form1"); 
    let inp = document.getElementById("amount"); 
    let t1 = document.getElementById("tovar1"); 
    let t2 = document.getElementById("tovar2"); 
    let t3 = document.getElementById("tovar3"); 
    let select = document.getElementById("grade"); 
    let o1 = document.getElementById("o1"); 
    let o2 = document.getElementById("o2"); 
    let o3 = document.getElementById("o3"); 
    let ptr = 0;

    function cost(id) {
        let p = inp.value;
        if (/^\d+$/.test(p) && p >= 0) {
            p = Number(p);
            let s = 0;
            let ss;
            switch (id) {
            case 1:
                s = p * t1.getAttribute("value");
                break;
            case 2:
                ss = select.options[select.selectedIndex];
                s = p * ss.getAttribute("value");
                break;
            case 3:
                s = p * t3.getAttribute("value");
                if (o1.checked) {
                    s += p * o1.getAttribute("value");
                }
                if (o2.checked) {
                        s += p * o2.getAttribute("value");
                }
                if (o3.checked) {
                    s += p * o3.getAttribute("value");
                }
                break;
            }
            document.getElementById("result1").innerHTML = s;
        } else {
            document.getElementById("result1").innerHTML = "введите корректные данные";
        }
    }
    t1.addEventListener("click", function () {
            document.getElementById("autor").classList.add("hide");
            document.getElementById("box").classList.add("hide");
            ptr = 1;
            cost(1);
    });
    t2.addEventListener("click", function () {
            document.getElementById("autor").classList.remove("hide");
            document.getElementById("box").classList.add("hide");
            ptr = 2;
            cost(2);
    });
    t3.addEventListener("click", function () {
            document.getElementById("autor").classList.add("hide");
            document.getElementById("box").classList.remove("hide");
            ptr = 3;
            cost(3);
    });
        
    select.addEventListener("change", function () {
            cost(2);
    });
        
    o1.addEventListener("click", function () {
            cost(3);
    });
    o2.addEventListener("click", function () {
            cost(3);
    });
    o3.addEventListener("click", function () {
            cost(3);
    });
        
    inp.addEventListener("input", function () {
            cost(ptr);
    });
        
    form.addEventListener("submit", function (fun) {
            fun.preventDefault();
    });
};
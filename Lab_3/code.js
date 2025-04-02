window.onload = function () { 
    let form = document.getElementById("form"); 
    let inp = document.getElementById("amount"); 
    let kn1 = document.getElementById("knopka1"); 
    let kn2 = document.getElementById("knopka2"); 
    let kn3 = document.getElementById("knopka3"); 
    let select = document.getElementById("zakazik"); 
    let gl1 = document.getElementById("gl1"); 
    let gl2 = document.getElementById("gl2"); 
    let gl3 = document.getElementById("gl3"); 
    let ptr = 0;

    function cost(id) {
        let p = inp.value;
        if (/^\d+$/.test(p) && p >= 0) {
            p = Number(p);
            let s = 0;
            let ss;
            switch (id) {
            case 1:
                s = p * kn1.getAttribute("price");
                break;
            case 2:
                ss = select.options[select.selectedIndex];
                s = p * ss.getAttribute("price");
                break;
            case 3:
                s = p * kn3.getAttribute("price");
                if (gl1.checked) {
                    s += p * gl1.getAttribute("price");
                }
                if (gl2.checked) {
                        s += p * gl2.getAttribute("price");
                }
                if (gl3.checked) {
                    s += p * gl3.getAttribute("price");
                }
                break;
            }
            document.getElementById("result").innerHTML = s;
        } else {
            document.getElementById("result").innerHTML = "а колво товаров?";
        }
    }
    kn1.addEventListener("click", function () {
            document.getElementById("sel").classList.add("hide");
            document.getElementById("boxik").classList.add("hide");
            ptr = 1;
            cost(1);
    });
    kn2.addEventListener("click", function () {
            document.getElementById("sel").classList.remove("hide");
            document.getElementById("boxik").classList.add("hide");
            ptr = 2;
            cost(2);
    });
    kn3.addEventListener("click", function () {
            document.getElementById("sel").classList.add("hide");
            document.getElementById("boxik").classList.remove("hide");
            ptr = 3;
            cost(3);
    });
        
    select.addEventListener("change", function () {
            cost(2);
    });
        
    gl1.addEventListener("click", function () {
            cost(3);
    });
    gl2.addEventListener("click", function () {
            cost(3);
    });
    gl3.addEventListener("click", function () {
            cost(3);
    });
        
    inp.addEventListener("input", function () {
            cost(ptr);
    });
        
    form.addEventListener("submit", function (fun) {
            fun.preventDefault();
    });
};

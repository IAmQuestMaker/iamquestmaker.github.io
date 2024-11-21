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

$(document).ready(function(){
    $('.multiple-items').slick({
        infinite: false,
        dots: true,
        slidesToShow: 3,
        speed: 300,
        slidesToScroll: 1,
        adaptiveHeight: false,
        responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
              }
            }
          ]
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const openModalButton = document.getElementById('open-modal');
    const closeModalButton = document.getElementById('close-modal');
    const feedbackForm = document.getElementById('feedback-form');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');

    // Восстановление данных из LocalStorage
    const restoreFormData = () => {
        document.getElementById('name').value = localStorage.getItem('name') || '';
        document.getElementById('email').value = localStorage.getItem('email') || '';
        document.getElementById('phone').value = localStorage.getItem('phone') || '';
        document.getElementById('organization').value = localStorage.getItem('organization') || '';
        document.getElementById('message').value = localStorage.getItem('message') || '';
        document.getElementById('consent').checked = localStorage.getItem('consent') === 'true';
    };

    restoreFormData();
    const saveFormData = () => {
        const fields = ['name', 'email', 'phone', 'organization', 'message', 'consent'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                if (field === 'consent') {
                    localStorage.setItem(field, element.checked);
                } else {
                    localStorage.setItem(field, element.value);
                }
            }
        });
    };

    const formElements = feedbackForm.querySelectorAll('input, textarea');
    formElements.forEach(element => {
        element.addEventListener('input', saveFormData);
    });

    // Открытие попапа
    openModalButton.addEventListener('click', () => {
        modal.classList.add('active');
        history.pushState(null, '', '#feedback');
    });

    // Закрытие попапа
    closeModalButton.addEventListener('click', closeModal);
    window.addEventListener('popstate', closeModal);

    function closeModal() {
        modal.classList.remove('active');
        history.replaceState(null, '', window.location.pathname); // Убираем хеш #feedback
    }
    
    feedbackForm.addEventListener('submit', (e) => {
        e.preventDefault();

 
        const Data = new FormData(feedbackForm);
        fetch('https://formcarry.com/s/sTDxquIyhWT', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify(Object.fromEntries(Data.entries()))
        })
        .then(response => {
            if (response.ok) {
                successMessage.style.display = 'block';
                errorMessage.style.display = 'none';
                // Очистка данных
                feedbackForm.reset();
                localStorage.clear();
            } else {
                errorMessage.style.display = 'block';
                successMessage.style.display = 'none';
            }
        })
        .catch(error => {
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
            console.error('Ошибка:', error);
        }); 

    });
});
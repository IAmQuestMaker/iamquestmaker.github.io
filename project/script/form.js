/*node browser: true */ /*global $ */ /*global alert */
/*global updateContent */
window.addEventListener("DOMContentLoaded", function () {
    /*
        window.onload = function () {
            const storedFio = localStorage.getItem("field-fio");
            const storedEmail = localStorage.getItem("field-email");
            const storedMessage = localStorage.getItem("field-message");
            const storedNumber = localStorage.getItem("field-number");
            const storedOrg = localStorage.getItem("field-org");
    
            if (storedFio) {
                document.getElementsByName("field-fio")[0].value = storedFio;
            }
            if (storedEmail) {
                document.getElementsByName("field-email")[0].value = storedEmail;
            }
            if (storedMessage) {
                document.getElementsByName("field-message")[0].value =
                storedMessage;
            }
            if (storedNumber) {
                document.getElementsByName("field-number")[0].value = storedNumber;
            }
            if (storedOrg) {
                document.getElementsByName("field-org")[0].value = storedOrg;
            }
        };
    
        const form = document.getElementById("myform");
    
        form.addEventListener("input", function () {
            const fields = [
                "field-fio",
                "field-email",
                "field-number",
                "field-org",
                "field-message"
            ];
            fields.forEach(function (field) {
                localStorage.setItem(
                    field,
                    document.getElementsByName(field)[0].value
                );
            });
        });
    */
        $(function () {
            $(".formcarryForm").submit(function (e) {
                e.preventDefault();
    
                let email = document.getElementsByName("field-email");
                let name = document.getElementsByName("field-fio");
                let number = document.getElementsByName("field-number");
                const checkbox = document.getElementById("formcheck");
                let formcheck = true;
                if (!name[0].value) {
                    formcheck = false;
                }
                if (!email[0].value) {
                    formcheck = false;
                }
                if (!number[0].value) {
                    formcheck = false;
                }
                if (!checkbox.checked) {
                    formcheck = false;
                }
    
                if (formcheck) {
                    $.ajax({
                        complete: function () {
                            document.getElementById("myform").reset();
                        },
                        contentType: false,
                        data: new FormData(this),
                        dataType: "json",
                        error: function (jqXHR) {
                            const errorObject = jqXHR.responseJSON;
    
                            alert("Ошибка: " + errorObject.message);
                        },
                        processData: false,
                        success: function (response) {
                            if (response.status === "success") {
                                alert("Форма отправлена!");
                                document.getElementById("myform").reset();
                            } else {
                                alert("Ошибка");
                                document.getElementById("myform").reset();
                            }
                        },
                        type: "POST",
                        url: "https://formcarry.com/s/_zyy2Llap8Z"
                    });
                } else {
                    alert("Заполните обязательные поля формы");
                }
            });
        });
    });
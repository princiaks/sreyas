
//////////////////////////////////////////////


$(document).ready(function(e){
  $(function () {
    $.validator.setDefaults({
      submitHandler: function () {
        
      }
    });
    $('#registrationform').validate({
      rules: {
        email_id: {
          required: true,
          email: true,
        },
        email_id1: {
          required: true,
          email: true,
          equalTo : "#email_id"
        },
        password: {
          required: true,
          minlength: 5
        },
        password1 : {
          minlength : 5,
          equalTo : "#password"
        },
        terms: {
          required: true
        },
      },
      messages: {
        email_id: {
          required: "Please enter a email address",
          email: "Please enter a vaild email address"
        },
        email_id1: {
          required: "Please enter a email address",
          email: "Please enter a vaild email address",
          equalTo :"Email ID not same"
        },
        password: {
          required: "Please provide a password",
          minlength: "Your password must be at least 5 characters long"
        },
        password1: {
          required: "Please provide a password",
          minlength: "Your password must be at least 5 characters long",
          equalTo :"Password Mismatch"
        },
        terms: "Please accept our terms"
      },
      errorElement: 'span',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.form-group').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      }
    });
  });
})

$('#registrationform').submit(function(e){
  e.preventDefault();
  if($(this).valid()==true){
    var data=new FormData(this);
    var action='register-user';
    ajaxcall(data,action,function(data)
    {
      swaltext(data);
    });
  }
 
});

  function ajaxcall(formElem,ajaxurl,handle)
  {
    $.ajax({
      url: base_url+ajaxurl,
      type: 'POST',
      data:formElem,
      processData:false,
      contentType:false,
      cache:false,
      async:false,
      success: function(data) {
        handle(data);
      }
  });
  }
  function ajaxcall1(data,ajaxurl,handle)
  {
    $.ajax({
      url: base_url+ajaxurl,
      type: 'POST',
      data:data,
      datatype:'json',
      success: function(data) {
        handle(data);
      }
  });
  }

  function swaltext(data)
  {
    var data=JSON.parse(data);
    swal(data.title,data.msg,data.status);
    if(data.redirect){window.location.href=base_url+data.redirect}
  }
  //////////////////////////////////////



 
  

 
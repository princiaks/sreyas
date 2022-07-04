
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


$('#add_products_btn').click(function(e)
{
  e.preventDefault();
  $("#add_products").validate({   
    rules: {
      name: {
          required: true,
          //lettersonly: true
      },
      price: {
          required: true,
          maxlength: 5

      },
      stock: {
        required: true,
        maxlength: 5
    }
    }
     
    });
    if($("#add_products").valid()==true){
      $("#add_products").submit();
    }


})

$('.productname').keyup(function(){
  var Text = $(this).val();
  Text = Text.toLowerCase();
  Text = Text.replace(/[^a-zA-Z]+/g,'');
$(".productname").val(Text);    
});

$('#add_products').submit(function(e){
  e.preventDefault();
  var data=new FormData(this);
  var action=$(this).attr('action');
  ajaxcall(data,action,function(data)
  {
    swaltext(data);
  });
});


$('.addtocart').click(function(e){
  var id=$(this).attr('id');
  var html="";
  var data={product_id:id,product_qty:$('#product_qty'+id).val()};
  ajaxcall1(data,'add_to_cart',function(data){
    var data=JSON.parse(data);
    if(data.result==0)
    {
      
     $.each(data.cartlist,function(index,value){
      html+=`  
      <tr class="cart_row">
      <td>`+value.product_name+`</td>
      <td>USD `+value.product_price+`</td>
      <td>`+value.product_count+`</td>
      <td>USD `+value.product_total+`</td>
<td>
          
            <span class="table-remove">
            <input type="hidden" name="delid" class="delid" value="`+value.product_id+`"><button type="button"
            class="btn iq-bg-danger btn-rounded btn-sm my-0 remove-product">Delete</button></span>
           
      </td> 
  
      </tr>`;
     });
     html+=`<tr>
     <td colspan="4">Grand Total:</td>
     <td class="grand_total"> USD `+data.carttotal+`</td>
     </tr>`;
   $('.cartlist').html(html);
   swal({
    text: "Item added to Cart",
    icon: "success",
    timer: 1000
 });

 
    }
    else if(data.result==1)
    {
      swal(" Sorry.. Only "+data.stock+" Left");
    }
    
  });
  });

  $('.cartlist').on('click','.remove-product',function(e){
    var thiselem=$(this);
    var data={cart_id:$(this).siblings('.delid').val()};
    ajaxcall1(data,'deleteproduct_from_cart',function(data)
    {
      thiselem.closest('tr').remove();
      $('.grand_total').html('USD '+data);
    }); 
  
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



 
  

 
<div id="content-page" class="content-page">
     
<div class="container-fluid">

<?php

$id=$name=$selling_price=$lbl_name=$lbl_price=$lbl_stock=$stock="";
$title='Add Product';

$action='add_products';

$button='Submit';

?>

    <div class="row">

       <div class="col-lg-12">

           <div class="iq-card">

                <div class="iq-card-header d-flex justify-content-between">

                   <div class="iq-header-title">

                      <h4 class="card-title"><?php echo $title;?></h4>

                   </div>
                   <div class="iq-header-title">
                    
                  </div>

                </div>

                <div class="iq-card-body">

                   

                   <form id="add_products" method="POST" action="<?php echo $action;?>" data-form="ajaxform" enctype="multipart/form-data">

                      <div class="form-row">

                         <div class="col">

                         <label><?php echo $lbl_name;?></label>

                            <input type="text" class="form-control productname" placeholder="Name" name="name" value="<?php echo $name;?>" required>

                         </div>     
                     
                      </div>
                      
                    
                      <div class="form-row">
                     
                     <div class="col">

                     <label><?php echo $lbl_price?></label>

                              <input type="number" class="form-control" placeholder="Price" name="price" step="0.001" value="<?php echo $selling_price;?>" required>

                           </div>
                           <div class="col">

                        <label><?php echo $lbl_stock?></label>

                                 <input type="number" class="form-control" placeholder="Stock" name="stock" value="<?php echo $stock;?>" step="0.001" required>

                              </div>
                          
                                                   

                      </div>
                    
                    
                   
              
                    <div class="form-row" style="padding-top:50px;">

                   
                  <div class="col">

                  <input type="hidden" name="status" value="In Stock">

                  <input type="hidden" name="id" value="<?php echo $id; ?>">
               
                              <button type="button" id="add_products_btn" class="btn btn-primary"><?php echo $button;?></button>

                      <!-- <button type="submit" class="btn iq-bg-danger">cancel</button> -->

                         </div>

                

                 

                    </div>                  

                   </form>

                </div>

             </div></div>

    </div>

 </div>

</div>


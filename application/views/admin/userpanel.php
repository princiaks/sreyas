
      <!-- Page Content  -->
      <div id="content-page" class="content-page">
     
         <div class="container-fluid">
         <div class="row">
            <div class="col-md-6">
                  <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                           <h4 class="card-title">UserPanel</h4>
                        </div>
                      
                     </div>
                     <div class="iq-card-body">
                        <div class="table-responsive">
                           <table class="table mb-0 table-borderless">
                             <thead>
                               <tr>
                                 <th scope="col">Name</th>
                                 <th scope="col">Price</th>
								<th scope="col">Quantity</th>
                               </tr>
                             </thead>
                             <tbody>
                                 <?php 
                                 $i=1;
                                 if(isset($productlist))
                                 if($productlist)
                                 {
                                 foreach($productlist as $details)
                                 {
                                ?>
                                 <tr>
                                 <td><?php echo $details->name?>
                                 <input type="hidden" name="id" id="id" value="<?php echo $details->id;?>"></td>
                                 <td><?php echo 'USD '.$details->price?>
                                 <td><select class="form-control product_qty" id="product_qty<?php echo $details->id;?>" name="product_qty">

                                    <?php if($details->stock)
                                    {
                                        for($i=1;$i<=$details->stock;$i++)
                                        {
                                            ?>
                                            <option value="<?php echo $i?>"><?php echo $i;?></option>
                                            <?php
                                        }
                                    }

                                   



                                    ?>
                                    </select>
                                </td>
								         <td>

                                       <span class="table-remove"><button type="button"
                                       class="btn iq-bg-danger btn-rounded btn-sm my-0 addtocart" id="<?php echo $details->id;?>">Add to Cart</button></span>       
                                      
                                 </td> 
                                <!--  <td>
                                   <span class="table-remove"><button type="button"
                                       class="btn iq-bg-danger btn-rounded btn-sm my-0">Hide</button></span>
                                 </td> -->
                                 </tr>

                                 <?php
                                 $i++;
                                 }
                                }
                                 ?>
                             </tbody>
                           </table>
                         </div>
                     </div>
                  </div>
               </div>
              
            </div>
         
            <div class="row">
               <div class="col-md-6">
                  <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
                     <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                           <h4 class="card-title">cartlist</h4>
                        </div>
                      
                     </div>
                     <div class="iq-card-body">
                        <div class="table-responsive">
                           <table class="table mb-0 table-borderless">
                             <thead>
                               <tr>
                                 <th scope="col">Item Name</th>
                                 <th scope="col">Price</th>
								         <th scope="col">Quantity</th>
                                 <th scope="col">Total</th>
                                 <th scope="col">Action</th>
								        <!-- <th scope="col">View</th> -->
                               </tr>
                             </thead>
                             <tbody class="cartlist">
                                 <?php 
                                 if($cart_list)
                                 {
                                 foreach($cart_list as $index=>$details)
                                 {
                                ?>
                                 <tr class="cart_row">
                                 <td><?php echo $details['product_name']?></td>
                                 <td><?php echo 'USD '.$details['product_price']?></td>
                                 <td><?php echo $details['product_count']?></td>
                                 <td><?php echo 'USD '.$details['product_total']?></td>
								         <td>
                                     
                                       <span class="table-remove"><input type="hidden" name="delitem" class="delitem" value="<?php echo $details['product_name'];?>">
                                       <input type="hidden" name="delid" class="delid" value="<?php echo $details['product_id'];?>"><button type="button"
                                       class="btn iq-bg-danger btn-rounded btn-sm my-0 remove-product">Delete</button></span>
                                      
                                 </td> 
                             
                                 </tr>

                                 <?php
                                 $i++;
                                 }
                                }
                                 ?>
                                 <tr>
                                     <td colspan="4">Grand Total:</td>
                                     <td class="grand_total"> USD <?php echo($this->session->userdata('cart_total')?$this->session->userdata('cart_total'):0)?></td>
                                 </tr>
                             </tbody>
                           </table>
                         </div>
                     </div>
                  </div>
               </div>
               
            </div>
         </div>
      </div>
  
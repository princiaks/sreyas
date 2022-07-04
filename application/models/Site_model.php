<?php

use LDAP\Result;

class Site_model extends CI_Model {

public function __construct()
{
        $this->load->database();
}
public function check_user_exist($username,$password)
{
$sucess=1;        
$query=$this->db->query("select id,username,mobile,name,email_id,role from user_details where mobile='$username' and password='$password'");
$result=$query->row();
if($result)
{
        $userdata=array(
                'user_id'=>$result->id,
                'username'=>$result->username,
                'name'=>$result->name,
                'email_id'=>$result->email_id,
                'mobile'=>$result->mobile,
                'role'=>$result->role
        );

        $this->session->set_userdata('userdata',$userdata);
        $this->session->set_userdata('user_id',$result->id);
      
        $sucess=1;
}
else
{
        $sucess=0;
}

return $sucess;
}
public function check_username_exist($username)
{
$sucess=1;        
$query=$this->db->query("select mobile from tbl_user_details where mobile='$username' and role='customer'");
$result=$query->row();
if($result)
{
      
        return 1;
}
else
{
        return 0;
}
}
public function is_user_loggedin()
 {
         if($this->session->userdata('user_id') && $this->session->userdata('user_id')!=1)
         {

                return 1;
         }
         else
         {
                 return 0;
         }
 }
 public function insert_user($data)
 {
        $result= $this->db->insert('tbl_user_details',$data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
 }

 public function get_homelists()
 {
         $data=array();
         $data['slider_list']=$this->get_sliders('main');
         $data['service_list']=$this->get_service_list();
         $data['moreservices']=$this->get_more_services();
        /*  $data['product_list']=$this->get_product_list(); */
        // $data['offers']=$this->get_offers();
       /*   $data['offer_products']=$this->get_offer_products(); */
        return $data;
 }
 public function get_food_homelists()
 {
        $data=array();
        //$data['slider_list']=$this->get_sliders('food');
        $data['category_list']=$this->get_food_categories();
        //$data['product_list']=$this->get_foodaddtohome_list();
        $data['trending_list']=$this->get_food_list();
        if(isset($_SESSION['current_location']['distance']) && $_SESSION['current_location']['distance']<=10)
        {
        $data['restaurant_list']=$this->get_restaurant_list($_SESSION['current_location']['latitude'],$_SESSION['current_location']['longitude']);     
        }
        else
        {
        $data['restaurant_list']=$this->get_restaurant_list();
        }
        foreach($data['restaurant_list'] as $index=>$value)
        {
                $value->disable_item="";
                if(strtotime(date('H:i',time())) < strtotime(date('H:i',strtotime($value->opening_time)))  ||  strtotime(date('H:i',time())) > strtotime(date('H:i',strtotime($value->closing_time))) || $value->visibility==2 )
                {
                        $value->disable_item="disable_item";
                }
        }
        $data['restaurant_count']=$this->get_restaurant_count();
        $data['slider_list']=$this->get_sliders('food');
        //$data['offers_products']=$this->get_offer_products('food');
        $data['deals']=$this->get_deals('food');
      
      /*   $data['offer_products']=$this->get_offer_products(); */
       return $data;
 }
 public function get_grocery_homelists()
 {
        $data=array();
        $data['slider_list']=$this->get_sliders('grocery');
        $data['category_list']=$this->get_grocery_categories(10);
        $where=array(
                'top_deals_status'=>0
        );
        $data['product_list']=$this->get_groceryproduct_list($where);
        $data['deals']=$this->get_deals('grocery');
        //$data['offers']=$this->get_offers();
      /*   $data['offer_products']=$this->get_offer_products(); */
       return $data;
 }
 public function get_service_details($link)
 {
        $result=$this->db->select('tbl_sub_service_categories.*,tbl_service_categories.name as parent_service')
        ->from('tbl_sub_service_categories')
        ->join('tbl_service_categories', 'tbl_service_categories.id=tbl_sub_service_categories.service_category_id')
        ->where('tbl_sub_service_categories.link =',$link)
        ->where('tbl_sub_service_categories.status !=','Deleted')
        ->get()->row();
        return $result;  
 }
 public function search_restaurantor_products($key)
 {
$result=array();
$result['restaurants']=$this->db->select('id,name,slug,tags')->from('tbl_restaurant_details')->like('name',$key,'both')->where('status !=','Deleted')->where('visibility !=',0)->get()->result();
$result['foods']=$this->db->select('tbl_food_products.id,tbl_food_products.name,tbl_food_products.image_url,tbl_food_products.slug,tbl_restaurant_details.name as restaurant')->from('tbl_food_products')->join('tbl_restaurant_details', 'tbl_restaurant_details.id = tbl_food_products.restaurant_id')->like('tbl_food_products.name',$key,'both')->where('tbl_food_products.status !=','Deleted')->where('tbl_food_products.visibility !=',0)->get()->result();
return $result;
 }
public function search_grocerycategory_products($key)
{
$result=array();
$result['grocerycat']=$this->db->select('id,name,slug')->from('tbl_grocery_categories')->like('name',$key,'both')->where('status !=','Deleted')->where('visibility !=',0)->get()->result();
$result['grocery']=$this->db->select('tbl_grocery_products.id,tbl_grocery_products.name,tbl_grocery_products.image_url,tbl_grocery_products.slug,tbl_grocery_categories.name as category')->from('tbl_grocery_products')->join('tbl_grocery_categories', 'tbl_grocery_categories.id = tbl_grocery_products.grocery_category_id')->like('tbl_grocery_products.name',$key,'both')->where('tbl_grocery_products.status !=','Deleted')->where('tbl_grocery_products.visibility !=',0)->where('tbl_grocery_categories.status !=','Deleted')->get()->result();
return $result;      
}
public function get_restaurant_list($latitude="10.389360",$longitude="76.157300")
{
        $result=$this->db->query("select * from (select *, SQRT(POW(69.1 * (loc_latitude - ".$latitude."), 2) + POW(69.1 * ((loc_longitude - ".$longitude.") * COS(loc_latitude / 57.3)), 2)) AS distance FROM tbl_restaurant_details) as vt where vt.distance < 25 and status !='Deleted' and visibility !=0 order by visibility asc,featured_status DESC,distance ASC limit 10")->result();
        
        return $result;     
}
public function get_all_restaurants($latitude="10.389360",$longitude="76.157300")
{
        $result=$this->db->query("select * from (select *, SQRT(POW(69.1 * (loc_latitude - ".$latitude."), 2) + POW(69.1 * ((loc_longitude - ".$longitude.") * COS(loc_latitude / 57.3)), 2)) AS distance FROM tbl_restaurant_details) as vt where vt.distance < 25 and status !='Deleted' and visibility !=0 order by visibility asc,featured_status DESC,distance ASC ")->result();
        return $result;     
}
public function get_restaurant_count()
{
        $result=$this->db->select('count(*) as count')->from('tbl_restaurant_details')->where('status !=','Deleted')->where('visibility !=',0)->get()->row();
        return $result->count;
}
public function get_food_categories($restaurant_id="")
{
        $this->db->select('tbl_food_category_master.*')
        ->from('tbl_food_category_master');
        if($restaurant_id)
        {
        $this->db->distinct();        
        $this->db->join('tbl_food_products','tbl_food_products.food_category_id = tbl_food_category_master.id')->where('tbl_food_products.restaurant_id',$restaurant_id);
        $this->db->where('tbl_food_products.status !=','Deleted');
        $this->db->where('tbl_food_products.visibility !=',0);
        }
        $this->db->where('tbl_food_category_master.status !=','Deleted');
        $result= $this->db->get()->result();
        return $result;
}
public function get_foodaddtohome_list()
{
        $result=$this->db->select('*')
        ->from('tbl_food_products')
        ->where('status !=','Deleted')
        ->where('add_to_home =',1)
        ->where('visibility =',0)
        ->get()->result();
        return $result;
}
public function get_food_list($restaurant_id="",$category_id='')
{
        $this->db->select('tbl_food_products.*,tbl_food_category_master.name as category')
        ->from('tbl_food_products')
        ->join('tbl_food_category_master','tbl_food_products.food_category_id = tbl_food_category_master.id')
        ->where('tbl_food_products.status !=','Deleted')
        ->where('visibility',1);
        // ->where('tbl_food_products.offer_status !=','offer');
        if($category_id=="" && $restaurant_id=="")
        {
         $this->db->where('trending_status =',1);
        }
        else if($restaurant_id!="" && $category_id=="")
        {
        // $this->db->where('restaurant_id',$restaurant_id);
        $this->db->where("restaurant_id=$restaurant_id and (trending_status =1 OR featured_status=1)");        
        //$this->db->or_where('featured_status',1);        
              
        }
        else if($restaurant_id && $category_id)
        {
        $this->db->where('restaurant_id',$restaurant_id);  
        $this->db->where('food_category_id',$category_id);     
        }
       
        $result=$this->db->get()->result();
        foreach($result as $index=>$value)
        {
        if(!empty($value->addons) && $value->addons !='null')
        {
                
        $addonids=implode(',',json_decode($value->addons));        
        $value->addons=$this->get_addons($addonids);
        }
        }
        return $result;
}

public function get_grocery_list($category_id="")
{
        $this->db->select('tbl_grocery_products.*,tbl_grocery_categories.name as category')
        ->from('tbl_grocery_products')
        ->join('tbl_grocery_categories','tbl_grocery_products.grocery_category_id = tbl_grocery_categories.id')
        ->where('tbl_grocery_products.status !=','Deleted')
        ->where('tbl_grocery_categories.status !=','Deleted')
        ->where('tbl_grocery_products.visibility',1)
        ->where('tbl_grocery_categories.visibility',1);
        if($category_id)
        $this->db->where('tbl_grocery_products.grocery_category_id',$category_id);
        return $result=$this->db->get()->result();
}

public function get_grocery_categories($limit="")
{
        $this->db->select('*')
        ->from('tbl_grocery_categories')
        ->where('status !=','Deleted');
        if($limit)
        $this->db->limit($limit);
        $result=$this->db->get()->result();
        return $result;
}
public function get_groceryproduct_list($where="")
{
        $this->db->select('*')
        ->from('tbl_grocery_products')
        ->where('status !=','Deleted')
        ->where('top_deals_status',1)
        ->where('visibility =',1);
        // if($where)
        // {
        // $this->db->where($where);
        // }
        $result=$this->db->get()->result();
        return $result;
}
 public function get_service_list()
 {
        $result=$this->db->select('*')
        ->from('tbl_service_categories')
        ->where('status !=','Deleted')
        ->where('name !=','More Services')
        ->get()->result();
        return $result;
 }

 public function get_more_services()
 {
         $result=$this->get_parentservice_details('more-services');
         if($result)
         return $this->get_sub_service_list($result->id);
         else
         return "";
 }
public function get_parentservice_details($link)
{
        $result="";
        $result=$this->db->select('id,name')->from('tbl_service_categories')->where('link',$link)->get()->row();
        return $result;
}
public function get_sub_service_list($servicecatid)
{
        $result=array();
        $this->db->select('*')
        ->from('tbl_sub_service_categories')
        ->where('status !=','Deleted');
        if($servicecatid)
        {
        $this->db->where('service_category_id',$servicecatid);
        }
        $result=$this->db->get()->result();
        return $result;
}

 public function get_sliders($slider_type="")
 {
         if($slider_type)
         {
                $slider_type=" and slider_type='$slider_type'";
         }
     $query   = $this->db->query("SELECT id,name,link,image_url FROM tbl_slider_details where status !='Deleted' $slider_type");
     $results = $query->result();
     return $results;
 }
public function delete_cart($id="")
{
        $where="";
        if($id != "")
        {
                $where="where id=$id";
        }
        $result=$this->db->query("delete from cart_details $where");
        return $result;

}
 public function get_product_categorylist()
 {
        $query   = $this->db->query("SELECT id,name,image_url FROM category_master where status !='Deleted'");
        $results = $query->result();
        return $results;
 }
 public function get_product_list($cat_id="")
 {
        $where="status !='Deleted' and visibility=1";
        if($cat_id!="")
        {
                $where="status !='Deleted' and category='$cat_id'";
        }
        $query   = $this->db->query("SELECT * FROM product_details where $where");
        $results = $query->result();
        foreach ($results as $index=>$value)
        {
            $value->stock=$this->get_stock_status($value->id);
            if($value->mrp > $value->price)
            {
                $value->discount= round(((($value->mrp)-($value->price))/($value->mrp))*100);
            }
        }
        return $results;
 }
 public function get_stock_status($product_id)
 {
     if($product_id != "")
     {
     $query=$this->db->query("SELECT max_sale FROM `product_secondary_details` where product_id=$product_id and max_sale <=0 and status !='Deleted'");
     if($query->num_rows()>0)
     {
         return 0;
     }
     else
     {
         return 1;
     }
 }
 else
 {
     return 0;
 }
 }


public function get_category_name($id)
{
        $query   = $this->db->query("SELECT name from category_master where id=$id");
        $results = $query->row();
        return $results->name;
}

public function get_variant_name($id="")
{
        if($id !="")
        {
        $query   = $this->db->query("SELECT name from variants_master where id=$id");
        $results = $query->row();
        return $results->name;
        }
        else
        {
         return null;        
        }
}

public function check_product_in_cart($product_id,$variants="")
{
        if($variants!="")
        {
                $where=" variants='$variants'";
        }
        else
        {
                $where=" product_id=$product_id";   
        }
        $query   = $this->db->query("SELECT id from cart_details where $where ");
        if($query->row())
        {
                return ($query->row())->id;
        }
        else
        {
                return 0;
        }
      
}

public function update_carted_item_details($data=array())
{
        $query   = $this->db->query("update carted_item_details set product_total = product_total + ".$data['product_total'].", product_count = product_count + ".$data['product_count']." where product_id=".$data['product_id']." and cart_id =".$data['cart_id']);
        if($this->db->affected_rows() >= 0)
        {
                return 1;
        }
        else
        {
                return 0;
        }

}



/* public function get_cart_list($param="")
{
        $data=array();
        $total=0;
        $count=0;
        $where="";
      /*   if($user_id !="")
        {
                $where= "where user_id=$user_id";
        }  */ /*
        $query   = $this->db->query("SELECT id FROM `cart_details` $where");
        $results = $query->result();
        
        $i=0;
        foreach($results as $id)
        {
               
                if($param=="carttotal")
                {
                $tot=$this->get_cart_sum($id->id);
                $total=$total+$tot['total'];
                $count=$count+$tot['count'];
                $data[0]['total']=$total;
                $data[0]['count']=$count;
                }
                else
                {
                        //SELECT sum(product_count) as product_count,sum(product_total) as product_total,sum(product_price) as product_price,product_variant,product_id,product_name,product_image,type FROM `carted_item_details` where cart_id in ('291','292','293') GROUP BY product_id,product_variant
                 $data[$i]=$this->get_carted_product_list($id->id);
                 
                }

                $i++;  
               
        }
        return $data;
} */

public function get_cart_list($param="")
{
        $result=array();
        $query   = $this->db->query("SELECT id FROM `cart_details`");
        $results = $query->result();
        foreach($results as $index=>$value)
        {
               $results[$index]=$value->id; 
        }
        $cart_ids=implode(',',$results);
       if($param=='carttotal')
       {
        if($cart_ids)
        {
        $query1=$this->db->query("select sum(product_total) as total, sum(product_count) as count from carted_item_details where cart_id in ($cart_ids)");
        $result=$query1->result();
        }
       

       }
       else
       {
        if($cart_ids)
        {
        $query1   = $this->db->query("SELECT id,cart_id,product_image,product_id,product_name,product_count,product_price,product_total,type,product_variant from carted_item_details where cart_id in ($cart_ids)");
         $result=$query1->result();
       
        }
     
       }
       
    
       
        return $result;

}

public function get_cart_sum($cart_id)
{
        $query   = $this->db->query("select sum(product_total) as total, sum(product_count) as count from carted_item_details where cart_id=$cart_id");

        //select sum(product_total) as total, sum(product_count) as count from carted_item_details where cart_id in('291','292','293')

        $res = $query->row();
        $data['total']=$res->total;
        $data['count']=$res->count;
        return $data;   
}



public function  get_carted_product_list($cart_id)
{
$query   = $this->db->query("SELECT id,cart_id,product_image,product_id,product_name,product_count,product_price,product_total,type from carted_item_details where cart_id=$cart_id");

$data = $query->result();
return $data;
}
public function  get_ordered_product_list($order_id)
{
        $query   = $this->db->query("SELECT id,cart_id,product_image,product_id,product_name,product_count,product_price,product_total,type from carted_item_details where order_id=$order_id");

        $data = $query->result();
        return $data;
}




/* public function get_cart_list($user_id)
{
        $where="";
        if($user_id !="")
        {
                $where= "where user_id=$user_id";
        }
        $query   = $this->db->query("SELECT id,product_id,name,variants,addon,addon_price,price,sum(quantity) as qty,sum(price) as tot_price,sum(total_amount) as total FROM `cart_details` $where GROUP BY variants,category,quantity,price ");

    
        $results = $query->result();
        foreach($results as $index=>$value)
        {
                $value->variants=$this->get_variant_name($value->variants);
                $value->addon=json_decode($value->addon);
                /* print_r($addonarray);  */
                /*foreach($value->addon as $index1=>$value1)
                {
                       $value1->addon_name=$this->get_addon_name($value1->addon_id);
                }
                $value->image_url=$this->get_product_image($value->product_id);
        }
        return $results;
}
 */

public function get_product_image($prod_id,$variant="")
{
        $table="product_details";
        if($variant != "" && $variant=="offer-product")
        {
                $table="offer_product_details";
        }
        $query   = $this->db->query("SELECT image_url from $table where id=$prod_id");
        $results = $query->row();
        return $results->image_url;

}

public function get_addon_name($adid)
{
        $query   = $this->db->query("SELECT name from addon_details where id=$adid");
        $results = $query->row();
        return $results->name;
}
public function get_single_product($prod_id)
{
      
        $query   = $this->db->query("SELECT * from product_details where id=$prod_id and status !='Deleted' ");
        $result = $query->row();
        return $result;   
}
public function get_single_offer_product($id)
{
        $query   = $this->db->query("SELECT * from offer_product_details where id=$id and status !='Deleted'");
        $result = $query->row();
        return $result;  
}
public function get_secondary_product_det($prod_id)
{       
        $query   = $this->db->query("SELECT variants,max_sale from product_secondary_details where product_id=$prod_id and status !='Deleted'");
        $result = $query->result();
        foreach($result as $index=>$value)
        {
                $result[$index]=array($value->variants,$this->get_variant_name($value->variants),$value->max_sale);
        }
      
        return $result;  
}

public function get_product_sec_details($data=array())
{
        $query   = $this->db->query("SELECT mrp,price,max_sale from product_secondary_details where product_id=$data[prod_id] and variants=$data[variant_id] and status !='Deleted'");
        $result = $query->result();
        return $result;  

}

public function get_user_details($user_id="")
{
        $result['address']  = $this->db->query("SELECT * from tbl_user_address_details where user_id=$user_id and status !='Deleted'")->result();
        $result['coincount']  = $this->db->query("SELECT * from tbl_user_coindetails where user_id=$user_id")->row();
        return $result;  
}


public function get_addons($addonids)
{ 
    $data=array();
    if($addonids=='')
    {
            $query=$this->db->select('id,name,selling_price,mrp,discount,order_limit')->from('tbl_food_addons')->where("status !='Deleted'");
   /*  $query   = $this->db->query("SELECT id,name,selling_price,mrp,discount,order_limit FROM tbl_food_addons where status !='Deleted'" ); */
    }
    else
    {
        $query=$this->db->select('id,name,selling_price,mrp,discount,order_limit')->from('tbl_food_addons')->where("status !='Deleted'")->where("id in ($addonids)");
   
    }
    $results = $query->get()->result();
   
   return $results; 
}
public function insert_cart($data=array())
{
        $result= $this->db->insert('cart_details',$data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
}

public function insert_carted_item_details($data=array())
{
        $result= $this->db->insert('carted_item_details',$data);
        return $result; 
}

public function check_promocode($id,$phoneno,$amount=100)
{
        $result   = $this->db->where(array('id'=>$id,'status !='=>'Deleted','status !='=>'Hidden'))->get('promocode_details')->result_array();
        if($result)
        {
                if(!empty($prod_ids=json_decode($result[0]['products'])))
                {
                        $name=array();
                        $i=0;
                        foreach($prod_ids as $ids)
                        {
                                $name[$i]=$this->get_product_name($ids);
                        }
                        $result[0]['product_names']=$name;
                }
            
               $qry=$this->db->query("select no_of_usage from promocode_user_details where promo_id=".$result[0]['id']." and allowed_users=".$phoneno);
               $row=$qry->row();
               if($row)
               {
                       /* print_r($qry->row()); exit; */
                       $result[0]['user_usage']=$row->no_of_usage;
               }
               else
               {

                        $tbldata=array(
                                'promo_id'=>$result[0]['id'],
                                'allowed_users'=>$phoneno,
                                'no_of_usage'=>$result[0]['no_of_usage']

                        );
                        $this->db->insert('promocode_user_details',$tbldata);
                   
                       if($this->db->affected_rows())
                       {
                       $result[0]['user_usage']=$result[0]['no_of_usage'];  
                       }
                       else
                       {
                        $result[0]['user_usage'] ="";   
                       }
               }
        }
        return $result;
    
}
public function get_promocodes()
{
        $query   = $this->db->query("SELECT promocode_details.id as promo_id,promocode_details.promo_code,offer_details.* from promocode_details join offer_details on promocode_details.offer_id=offer_details.id WHERE promocode_details.status !='Deleted' and promocode_details.status !='Hidden' and offer_details.status !='Deleted'");
        return $query->result();    
}
public function get_product_name($product_id)
{
        $qry=$this->db->query("select name from product_details where id=".$product_id);
        $res=$qry->row()->name;     
        return $res;
}
public function insert_usercoin_data($user_id)
{
        $result= $this->db->insert('tbl_user_coindetails',array('user_id'=>$user_id,'coin_count'=>0,'coin_value'=>0,'status'=>'Active'));
        $insert_id = $this->db->insert_id();
        return $insert_id;
}
public function update_user_additional_data($data=array())
{
        $this->db->where('id', $data['id']);
        $result= $this->db->update('user_add_details',$data);
}
public function insert_order_details($data=array())
{
        $result= $this->db->insert('tbl_order_details',$data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
}
public function insert_ordered_items($data=array())
{
        $this->db->insert('tbl_carted_item_details',$data);
}
public function update_carted_items($cart_id,$order_id)
{
        $this->db->query("update carted_item_details set order_id=$order_id where cart_id in ($cart_id)");
        $this->update_product_stock($cart_id); 
}

public function update_product_stock($cart_id)
{
        $qry=$this->db->query("select product_variant,product_count,product_id,type from carted_item_details where cart_id in ($cart_id)");
        $res=$qry->result();
        if($res)
        {
                foreach ($res as $detail)
                {
                if($detail->type=='product')
                {

                $qry1=$this->db->query("update product_secondary_details set max_sale=max_sale -".$detail->product_count." where product_id=".$detail->product_id." and variants=".$detail->product_variant);
                }
                else if($detail->type=='offer-product')
                {
                $qry1=$this->db->query("update offer_product_details set stock=stock -".$detail->product_count." where id=".$detail->product_id." and variants='".$detail->product_variant."'");        
                }
                else if($detail->type=='addon')
                {
                $qry1=$this->db->query("update addon_details set max_sale = max_sale - ".$detail->product_count." where id = ".$detail->product_id);   
                }
        }
        }
}
public function get_arealist()
{
        $query   = $this->db->query("SELECT id,name FROM area_master where status !='Deleted'");
        $results = $query->result();
        foreach ($results as $row)
        {
        $data[$row->id]=$row->name;
        }
        return $data;

 }

 /* public function get_delivery_charge($area)
 {
        $query   = $this->db->query("SELECT * from delivery_charge_master where area=$area and status !='Deleted'");
        $results = $query->row();
        if($results)
        {
        return $results;
        }
        else
        {
        return 0;
        }
 } */
 public function get_cart_items()
 {
        $query   = $this->db->query("SELECT name from cart_details");
        $result = $query->result();
        return json_encode($result);  
        
 }
 public function check_offer_product($user_id)
{
       /*  $query=$this->db->query("select no_of_usage from offer_product_user_details where product_id=".$product_id." and user_id=".$user_id." and no_of_usage <=0")->row(); */

       $return=0;
        if($user_id)
        {
        $query   = $this->db->query("SELECT cart_details.product_id,offer_product_details.no_of_usage from cart_details,offer_product_details where cart_details.variants='offer-product' and offer_product_details.id=cart_details.product_id ");
        $result = $query->row(); 
        if($result)  
        {
                $res1 =$this->db->query("SELECT no_of_usage from offer_product_user_details where  product_id=".$result->product_id." and user_id=".$user_id)->row();
                
                if($res1)
                {     
                $return=$res1->no_of_usage;
                }  
                else
                {
                        $insert=array(
                                'product_id'=>$result->product_id,
                                'user_id'=>$user_id,
                                'no_of_usage'=>$result->no_of_usage
                        );
                        $ins=$this->db->insert('offer_product_user_details',$insert);  
                        if($ins){$return=$result->no_of_usage;
                                print_r('ins'.$return); exit;} 
                }

        }
        }
       
       return $return;  
}
 

 public function get_cart_id()
 {
        $query   = $this->db->query("SELECT id from cart_details");
        $result = $query->result();
        return $result;
 }

 public function update_promocode_usage($promo_id,$phoneno)
 {
        $this->db->query("update promocode_user_details set no_of_usage=no_of_usage-1 where promo_id='$promo_id' and allowed_users='$phoneno' ");
        return $this->db->affected_rows();
 }

 public function update_offer_product_usage($product_id,$user_id)
 {
        $this->db->query("update offer_product_user_details set no_of_usage=no_of_usage-1 where product_id=".$product_id." and user_id=".$user_id);
        return $this->db->affected_rows();
 }

 public function deleteproduct_from_cart($data)
 {
         $actual_tot=$data['actual_tot'];
         $promo=array();
      
                $this->db->delete('cart_details',array('product_id'=>$data['product_id'],'variants'=>$data['product_variant']));
                $this->db->delete('carted_item_details',array('product_id'=>$data['product_id'],'product_variant'=>$data['product_variant']));
         
         if($this->session->userdata('promocode'))
         {
                $act_tot=$actual_tot-$data['product_total'];
                $promo=$this->session->userdata('promocode');
               
                foreach($promo as $index=>$value)
                {
                        if($value['promo_category']=='tcv')
                        {
                                $act_tot=$act_tot-$value['value'];
                        }
                        else if($value['promo_category']=='perc')
                        {
                                $act_tot=$act_tot-($act_tot*($value['value']/100));
                        }
                               
                } 
                if($this->session->userdata('cart_total'))
                {
                        $this->session->set_userdata('cart_total',$act_tot);
                }
               

         }
         else
         {
         if($this->session->userdata('cart_total'))
         {
                 $this->session->set_userdata('cart_total',$this->session->userdata('cart_total')-$data['product_total']);
         }
         
        }
        if($this->session->userdata('cart_value'))
         {
         $this->session->set_userdata('cart_value',$this->session->userdata('cart_value')-$data['product_count']);
         }  
         else
         {
          $this->session->set_userdata('cart_value',0); 
         }
        
 }

 public function update_carted_product_count($data=array())
 {
         $return['msg']="";
         $return['val']=0;
      
        if($data)
        {
                if($data['type']=='product')
                {
                $tot=$this->db->query("select carted_item_details.product_total,carted_item_details.product_count,carted_item_details.product_price,product_secondary_details.max_sale,product_details.status from carted_item_details INNER JOIN product_secondary_details on product_secondary_details.product_id=carted_item_details.product_id and product_secondary_details.variants=carted_item_details.product_variant LEFT JOIN product_details on product_details.id=product_secondary_details.product_id where cart_id=".$data['cart_id']);
                }
                else if($data['type']=='addon')
                {
                $tot=$this->db->query("select carted_item_details.product_total,carted_item_details.product_count,carted_item_details.product_price,addon_details.max_sale,addon_details.status from carted_item_details INNER JOIN addon_details on addon_details.id=carted_item_details.product_id where cart_id=".$data['cart_id']);      
                }
                $res=$tot->row();
                if($res->max_sale <= 0 || $res->status =="Out Of Stock")
                {
                        $return['msg']="Out Of Stock";
                        $return['val']=0;
                } 
                else if($data['quantity'] > $res->max_sale)
                {
                        $return['msg']="Only $res->max_sale Available";
                        $return['val']=$res->max_sale;
                }
                else
                {
            
               $this->db->query("UPDATE carted_item_details set product_total=(product_price *". $data['quantity']."), product_count=".$data['quantity']." WHERE cart_id=".$data['cart_id']);
               if($this->db->affected_rows())
               {
                      /*  $qry=$this->db->query("select product_total,product_count from carted_item_details where cart_id=".$data['cart_id']);
                       $res1=$qry->row(); */
                       if($this->session->userdata('cart_total'))
                       {
                              
                       $this->session->set_userdata('cart_total',($this->session->userdata('cart_total')-$res->product_total)+($res->product_price * $data['quantity']));
                       }
                       if($this->session->userdata('cart_value'))
                       {
                              
                       $this->session->set_userdata('cart_value',($this->session->userdata('cart_value')-$res->product_count)+$data['quantity']);
                       }

               }
        }
              /*  $this->db->where('cart_id', $data['cart_id']);
               $this->db->update('carted_item_details', array('product_count' => $data['quantity']));  */
        }
        return $return;
 }

 public function get_ordereditem_details($order_id)
 {
        $query   = $this->db->query("SELECT product_name,product_count,product_total from carted_item_details where order_id=$order_id");
        $data = $query->result();
        return $data;
 }

 public function get_user_additional_data($user_id)
 {
        $query   = $this->db->query("SELECT id,email_id,address,addresstype,street,landmark,area from user_add_details where user_id='$user_id' and role='customer'");
        if($query->row())
        {
        $data = $query->row();
        }
        else
        {
        $data="";
        }
        return $data;

 }

 public function get_offers($offer_type)
 {
        if($offer_type)
        {
               $offer_type=" and offer_type='$offer_type'";
        }
    $query   = $this->db->query("SELECT id,name,offer_url,image_url FROM tbl_offer_details where status !='Deleted' $offer_type");
    $results = $query->result();
    return $results;


 }
 public function get_offer_products($producttype)
 {
        $result=array();
        if($producttype=='food')
        {
         $result=$this->db->select('*')->from('tbl_food_products')->where('offer_status','offer')->get()->result();
        }
        else if($producttype=='grocery')
        {
        $result=$this->db->select('*')->from('tbl_grocery_products')->where('offer_status','offer')->get()->result();        
        }
        return $result;
 }
 public function get_deals($producttype)
 {
        $result=array();
        $result=$this->db->select('*')->from('tbl_food_deals')->where('deal_type',$producttype)->where('home_status',1)->where('status !=','Deleted')->get()->result();
        return $result;  
 }
 public function get_orders($user_id)
 {
        $query   = $this->db->query("SELECT id,order_total,items,order_time,status from tbl_order_details where customer_id=".$user_id." order by created_on desc limit 20");
        //.$user_id
        $data = $query->result();
        return $data; 
 }
 
 public function get_status_name($id)
 {
     $query   = $this->db->query("SELECT name from tbl_status_master where id=$id");
     $results = $query->row();
     if($results)
     {
         return $results->name;
     }
     else
     {
         return null;
     }

 }

 public function get_reward_point($user_id)
 {
        
     $query   = $this->db->query("SELECT reward_point from user_add_details where user_id=$user_id and role='customer'");
     $results = $query->row();
     if($results)
     {
         return $results->reward_point;
     }
     else
     {
         return 0;
     } 
 }
 public function get_single_order($order_id)
 {
         $data=array();
         $data['order_details']  = $this->db->select("*")->from('tbl_order_details')->where('order_id',$order_id)->get()->row();
         if($data['order_details'])
         {
                 $data['shipping_details']=$this->db->select('*')->from('tbl_user_address_details')->where('user_id',$data['order_details']->customer_id)->where('id',$data['order_details']->customer_address)->get()->row();
                 $data['status']=$this->get_status_name($data['order_details']->status);
                 $data['status_updatelist']=$this->admin_model->get_statusupdate_list($data['order_details']->id);
                 $data['status_list']=$this->admin_model->get_status_list($data['order_details']->order_type);
                 $data['item_details']=$this->site_model->get_ordered_items($order_id);
         if($data['order_details']->order_type=="food")
         {
         foreach($data['item_details'] as $index=>$value)
         {
                 $value->restaurant_id=$this->admin_model->get_name($value->restaurant_id,'tbl_restaurant_details');
         }
         }
         }
         
         
 
         return $data;
 }


 public function get_deliveryboy_name($id)
 {   
     $query   = $this->db->query("SELECT name,mobile from delivery_boy_details where user_id=$id");
     $results = $query->row();
     if($results)
     {
         return $results;
     }
     else
     {
         return null;
     }
    
 }

 public function check_user_email_exist($email="")
 {
        $query=$this->db->query("select id,email_id,name from tbl_user_details where email_id='".$email."'");
        $results=$query->row();
        if($results)
        {
                return $results;
        }
        else
        {
                return 0;
        }
 }

 public function update_password($data="")
 {
         $result="";
        if($data)
        {
                $this->db->where(array('email_id'=> $data['email']));
                $result= $this->db->update('user_details',array('password'=>$data['password']));

        }
        echo $this->db->affected_rows();
 }

 public function get_minimum_order()
 {
     $query   = $this->db->query("SELECT min_order,delivery_extra_charge FROM minimum_order_extra_delivery where status !='Deleted'");
     $results = $query->result();
     return $results;
 }
 public function get_user_location($user_id)
 {
      $query    =   $this->db->query("SELECT user_address_details.area_id,area_master.name,delivery_charge_master.* FROM `user_address_details`,area_master,delivery_charge_master WHERE area_master.id=user_address_details.area_id and user_address_details.user_id=$user_id and delivery_charge_master.area=user_address_details.area_id");
      if($query->row())
      {
              return $query->row();
      }
      else
      {
              return 0;
      }
 }
 public function update_user_location($user_id,$location)
 {
         $query=$this->db->query("update user_add_details set area=$location where user_id=$user_id");
 }
 public function add_new_address($data=array())
 {
         
        $result= $this->db->insert('user_address_details',$data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
 }
 public function update_address($data=array())
 {
        $this->db->where(array('user_id'=>$data['user_id'],'address_type'=>$data['address_type']));
        $result= $this->db->update('user_address_details',$data);
        if($this->db->affected_rows() >= 0)
        {
                return 1;
        }
        else
        {
                return 0;
        }
 }
 public function get_all_address($user_id)
 {
        $result    =   $this->db->query("SELECT user_address_details.*,delivery_charge_master.* FROM `user_address_details` join delivery_charge_master on delivery_charge_master.area=user_address_details.area_id  WHERE user_address_details.user_id=$user_id")->result();
        return $result;
 }
 public function check_address_exists($user_id,$address_type)
 {
        $result    =   $this->db->query("SELECT user_id FROM `user_address_details`  WHERE user_id=$user_id and address_type='$address_type'")->row();
        if($result)
        {
                return 1;
        }    
        else
        {
                return 0;
        }
 }
 
 public function add_area($area)
 {
         $result1=$this->db->query("select id from area_master where name like '%$area%' and status !='Deleted'")->row();
         if($result1)
         {
                 return $result1->id;
         }
         else
         {
                $result= $this->db->insert('area_master',array('name'=>$area,'status'=>'Active'));
                $insert_id = $this->db->insert_id();
                $result1=$this->db->insert('delivery_charge_master',array('area'=>$insert_id,'charge'=>2, 'min_order'=>0, 'extra_charge'=>0, `status`=>'new'));
                return $insert_id;   
         }
 }

 public function get_single_item($type,$slug,$prodtype="")
 {
         $result=array();
         if($type=="food")
         {
                $result=$this->db->query("SELECT *, 'food' as type FROM tbl_food_products WHERE slug='" . 
                $slug . "'")->row(); 
               if(! $result)
               {
                $result=$this->db->query("SELECT *, 'restaurant' as type FROM tbl_restaurant_details WHERE slug= '" . $slug . "'" )->row();
               
               }
             
                
         }
         else if($type=="grocery")
         {
                $result=$this->db->query("SELECT *, 'grocery' as type FROM tbl_grocery_products WHERE slug ='" . 
                $slug . "'")->row(); 
               if(! $result)
               {
                $result=$this->db->query("SELECT *, 'grocerycategory' as type FROM tbl_grocery_categories WHERE slug ='" . $slug . "'" )->row();
               
               }    
         }
         else if($type=="deals")
         {
                $result=$this->db->query("SELECT * FROM tbl_food_deals WHERE slug ='" . 
                $slug . "' and deal_type='".$prodtype."'")->row();      
         }
         return $result;
 }
 public function get_single_item_withid($columnlist,$id,$table,$where="")
 {
        $this->db->select($columnlist)->from($table)->where('id',$id)->where('status !=','Deleted');
        if($where)
        $this->db->where($where);
        return $result=$this->db->get()->row(); 
 }
 public function get_categorywise_foodlist($catslug)
 {
         $this->db->select('tbl_food_products.*,tbl_restaurant_details.name as restaurant')
         ->from('tbl_food_products')->join('tbl_restaurant_details','tbl_food_products.restaurant_id=tbl_restaurant_details.id','left')
         ->join('tbl_food_category_master','tbl_food_products.food_category_id=tbl_food_category_master.id','left')
         ->where('tbl_food_category_master.slug',$catslug)
         ->where('tbl_food_category_master.status !=','Deleted')
         ->where('tbl_food_products.visibility',1)
         ->where('tbl_food_products.status !=','Deleted')
         ->where('tbl_restaurant_details.status !=','Deleted')
         ->where('tbl_restaurant_details.visibility',1);
        return $this->db->get()->result();
 }

 public function get_all_vegetarianfoods()
 {
        $this->db->select('tbl_food_products.*,tbl_restaurant_details.name as restaurant')
        ->from('tbl_food_products')->join('tbl_restaurant_details','tbl_food_products.restaurant_id=tbl_restaurant_details.id','left')
        ->join('tbl_food_category_master','tbl_food_products.food_category_id=tbl_food_category_master.id','left')
        ->where('tbl_food_products.veg_nonveg_status','veg')
        ->where('tbl_food_category_master.status !=','Deleted')
        ->where('tbl_food_products.visibility',1)
        ->where('tbl_food_products.status !=','Deleted')
        ->where('tbl_restaurant_details.status !=','Deleted')
        ->where('tbl_restaurant_details.visibility',1);
       return $this->db->get()->result();  
 }
 public function get_nearest_restaurant_details($restaurant_id,$resulttype="")
 {
         $data['nearestlist']=$nearrestaurants=array();
       
         $result=$this->db->select('nearest_restaurants')->from('tbl_restaurant_details')->where('id',$restaurant_id)->get()->row();
        if($result && $result->nearest_restaurants)
        {
                $nearrestaurants=json_decode($result->nearest_restaurants);

                // load most popular 5 then option for view more
                if($resulttype !="count")
                {
                foreach($nearrestaurants as $rest_id)
                {
                        $data['nearestlist'][]=array(
                                'restaurant_details'=>$this->get_single_item_withid('*',$rest_id,'tbl_restaurant_details',' visibility=1'),
                        );
                }
        }
        }
        if($resulttype=="count")
        { return $nearrestaurants;}
        else
        { return $data;}
       
 }
 public function get_nearest_ids($restaurant_id)
 {
        $result="";
        $this->db->select('nearest_restaurants')->from('tbl_restaurant_details')->where('id',$restaurant_id);
        $result=$this->db->get()->row()->nearest_restaurants;
        return $result;
 }
 public function get_nearest_restaurants($restaurant_id)
 {
        $result=array();
        $ids=$this->get_nearest_ids($restaurant_id);
        if($ids)
        {
        $ids=implode(",",json_decode($ids));       
        $result=$this->db->select('*')->from('tbl_restaurant_details')->where('id in('.$ids.')')->where('status !=','Deleted')->where('visibility !=',0)->get()->result();
        }
return $result;
 }
 public function delete_user_withmobile($mobile)
 {
       $this->db->query("DELETE tbl_user_details, tbl_user_add_details FROM tbl_user_details INNER JOIN tbl_user_add_details
       WHERE tbl_user_details.id=tbl_user_add_details.user_id AND tbl_user_details.status='verify'");
 }
 public function make_useractive_withmob($mobile)
 {
         $login_string = hash('sha512', $mobile . time());
         $this->db->where('mobile',$mobile)->update('tbl_user_details',array('status'=>'Active','pomotoken'=>$login_string));
         if($this->db->affected_rows()>=0)
         {
        $cookie_name  = 'url_log_coo';  
        $cookie_value = $login_string;
        setcookie($cookie_name, $cookie_value, time() + (2592000 * 6), "/");
         return 1;
         }
         else
         return 0;
 }
 public function make_user_loggedin($mobile)
 {
         $userdet=$this->db->select('*')->from('tbl_user_details')->where('mobile',$mobile)->where('role','customer')->get()->row();
         if($userdet)
         {
                $cookie_name  = 'url_log_coo';  
                $cookie_value = $userdet->pomotoken;
                setcookie($cookie_name, $cookie_value, time() + (2592000 * 6), "/");  
                $this->set_userlogin_details($cookie_value);    
         }
 }
 public function set_userlogin_details($cookieval="")
 {   
       $this->db->select('id,name,mobile,email_id,role')->from('tbl_user_details')->where('status','Active');
       if($cookieval)
       {
         $this->db->where('pomotoken',$cookieval);   
       }   
       $userlog=$this->db->get()->row();
       if($userlog)
       {
       $this->session->set_userdata('pomo_userid',$userlog->id);
       $this->session->set_userdata('pomo_username',$userlog->name);
       $this->session->set_userdata('pomo_useremailid',$userlog->email_id);
       $this->session->set_userdata('pomo_usermobile',$userlog->mobile);
       $this->session->set_userdata('pomo_userrole',$userlog->role);
       }
       
 }

 public function get_user_address($user_id,$address_id="")
 {
         $result=array();
         if($address_id)
         {
        $result=$this->db->select('*')->from('tbl_user_address_details')->where('user_id',$user_id)->where('status !=','Deleted')->where('id',$address_id)->get()->row();
         }
         else
         {
        $result=$this->db->select('*')->from('tbl_user_address_details')->where('user_id',$user_id)->where('status !=','Deleted')->order_by("default_status", "desc")->get()->result();
         }
        return $result;
 }
 public function get_delivery_charge($distance)
 {
$charge=$this->db->select('charge')->from('tbl_delivery_charge_master')->where('range_from <=',$distance)->where('range_to >',$distance)->where('status !=','Deleted')->get()->row();
if($charge)
{
return $charge->charge;
}
else
{
return 30;
} 
}

public function insert_user_address($data)
{
        $result= $this->db->insert('tbl_user_address_details',$data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
}
public function update_user_address($data)
{
        $this->db->where('id', $data['id']);
        $result= $this->db->update('tbl_user_address_details',$data);
        if($this->db->affected_rows() >=0)
        return 1;
        else
        return 0;
}
public function delete_customer_address($address_id)
{
        $this -> db -> where('id', $address_id);
        $this -> db -> delete('tbl_user_address_details');

}
public function get_deliveryfrom_locations($restaurant_ids,$userloc)
{
        $delivery=0;

        if($restaurant_ids)
        {
                $reslocation=$this->db->select('loc_latitude,loc_longitude')->from('tbl_restaurant_details')->where('id in('.implode(',',$restaurant_ids).')')->get()->result();
                $restcount=count($reslocation);
                for($i=0;$i<$restcount;$i++)
                {
                if(isset($reslocation[$i+1]))
                {
                        $earthRadius=6371;
                        $latFrom = deg2rad(floatval($reslocation[$i]->loc_latitude));
                        $lonFrom = deg2rad(floatval($reslocation[$i]->loc_longitude));
                        $latTo = deg2rad(floatval($reslocation[$i+1]->loc_latitude));
                        $lonTo = deg2rad(floatval($reslocation[$i+1]->loc_longitude));
                  
                        $latDelta = $latTo - $latFrom;
                        $lonDelta = $lonTo - $lonFrom;
                  
                        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                          cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                        $distance=round($angle * $earthRadius); 
                        $delivery+=$this->get_delivery_charge($distance);

                }
                else
                {
                        if($userloc)
                        {
                                $earthRadius=6371;
                                $latFrom = deg2rad(floatval($reslocation[$i]->loc_latitude));
                                $lonFrom = deg2rad(floatval($reslocation[$i]->loc_longitude));
                                $latTo = deg2rad(floatval($userloc['latitude']));
                                $lonTo = deg2rad(floatval($userloc['longitude']));
                          
                                $latDelta = $latTo - $latFrom;
                                $lonDelta = $lonTo - $lonFrom;
                          
                                $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                                  cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
                                $distance=round($angle * $earthRadius); 
                                $delivery+=$this->get_delivery_charge($distance); 
                        }
                        break;
                }
                }
              
        }
       return $delivery;
}
// public function update_coin_count($coincount)
// {
//         $this->db->query("update tbl_user_coindetails set coin_count=coin_count + $coincount where user_id=".$this->session->userdata('pomo_userid'));
// }
public function get_deals_list($type,$id)
{
        $result=array();
        if($id)
        {
        $result=$this->db->query("select * from tbl_".$type."_products where deal_id=".$id." and status!='Deleted' and visibility=1")->result();
        }
        return $result;
}
public function get_singllerestaurant_order($restaurant_id,$order_id="")
{
        $result=array();
        if($order_id)
        {
                $result=$this->db->select('*')->from('tbl_carted_item_details')->where('order_id',$order_id)->where('restaurant_id',$restaurant_id)->get()->result();
        }
        else
        {
                $result=$this->db->select('*')->from('tbl_carted_item_details')->where('restaurant_id',$restaurant_id)->get()->result();
        }
        return $result;

}

public function get_ordered_items($order_id)
{
        $result=array();
        if($order_id)
        {
                $result=$this->db->select('*')->from('tbl_carted_item_details')->where('order_id',$order_id)->get()->result();
        }
        return $result;

}
public function get_my_orders($user_id,$status="")
{
        $result=array();
        $result['delivered']=$this->db->select('*')->from('tbl_order_details')->where('customer_id',$user_id)->where('status',4)->where('order_type !=','service')->order_by('created_on','DESC')->get()->result();
        $result['onprogress']=$this->db->select('*')->from('tbl_order_details')->where('customer_id',$user_id)->where('status in(1,2,3)')->where('order_type !=','service')->order_by('created_on','DESC')->get()->result();
        $result['canceled']=$this->db->select('*')->from('tbl_order_details')->where('customer_id',$user_id)->where('status in(5,9)')->where('order_type !=','service')->order_by('created_on','DESC')->get()->result();


        if($result['delivered'])
        {
                foreach($result['delivered'] as $index=>$value)
                {
                        $value->order_items=$this->get_ordered_items($value->id);
                        $value->status=$this->get_status_name($value->status);
                }
        }
        if($result['onprogress'])
        {
                foreach($result['onprogress'] as $index=>$value)
                {
                        $value->order_items=$this->site_model->get_ordered_items($value->id);
                        $value->status=$this->admin_model->get_status_name($value->status);
                }
        }
        if($result['canceled'])
        {
                foreach($result['canceled'] as $index=>$value)
                {
                        $value->order_items=$this->site_model->get_ordered_items($value->id);
                        $value->status=$this->admin_model->get_status_name($value->status);
                }
        }
        return $result;

}

public function get_myservice_requests($user_id,$status="")
{
        $result=array();
        $result['completedrequest']=$this->db->select('*')->from('tbl_order_details')->where('customer_id',$user_id)->where('status',8)->where('order_type','service')->order_by('created_on','DESC')->get()->result();
        $result['onprogress']=$this->db->select('*')->from('tbl_order_details')->where('customer_id',$user_id)->where('status in(6,7)')->where('order_type','service')->order_by('created_on','DESC')->get()->result();
        $result['canceled']=$this->db->select('*')->from('tbl_order_details')->where('customer_id',$user_id)->where('status in(10)')->where('order_type','service')->order_by('created_on','DESC')->get()->result();

        if($result['onprogress'])
        {
                foreach($result['onprogress'] as $index=>$value)
                {
                        $value->status=$this->admin_model->get_status_name($value->status);
                        $value->sub_service_id=$this->admin_model->get_name($value->sub_service_id,'tbl_sub_service_categories');
                }
        }
        if($result['completedrequest'])
        {
                foreach($result['completedrequest'] as $index=>$value)
                {
                        $value->sub_service_id=$this->admin_model->get_name($value->sub_service_id,'tbl_sub_service_categories');
                }
        }
        if($result['canceled'])
        {
                foreach($result['canceled'] as $index=>$value)
                {
                        $value->sub_service_id=$this->admin_model->get_name($value->sub_service_id,'tbl_sub_service_categories');
                }
        }
        return $result;
}

public function update_order_statusdetails($data)
{
        $sel=$this->db->select('id')->from('tbl_order_status_update')->where(array('status_id'=>$data['status_id'],'order_id'=>$data['order_id']))->get()->row();
        if($sel)
        {
                $this->db->where(array('id'=>$sel->id));
                $result= $this->db->update('tbl_order_status_update',$data);
                if($this->db->affected_rows() >= 0)
                return 1;
                else
                return 0;
        }
        else
        {
                $result= $this->db->insert('tbl_order_status_update',array('order_id'=>$data['order_id'],'status_id'=>$data['status_id']));
                $insert_id = $this->db->insert_id();
                return $insert_id;     
        }
        
}

public function update_addressdefault_status($user_id)
{
        $this->db->where(array('user_id'=>$user_id));
        $result= $this->db->update('tbl_user_address_details',array('default_status'=>0));
        if($this->db->affected_rows() >= 0)
        return 1;
        else
        return 0;   
}
public function delete_addresstype_ifexist($user_id,$address_type,$address_id="")
{

       $this->db->select('id')->from('tbl_user_address_details')->where('address_type',$address_type)->where('user_id',$user_id)->where('status !=','Deleted');
        if($address_id)
        {
          $this->db->where('id !=',$address_id);      
        }
        $result=$this->db->get()->row();
        if($result)
        {
                $this -> db -> where('id', $result->id);
                $this -> db -> delete('tbl_user_address_details');
                return 1;
        }
        else
        {
                return 0;
        }

}
}

?>
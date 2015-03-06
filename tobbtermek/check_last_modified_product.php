<?php
$recent_product_query = "SELECT 
                               p.productCode
                            FROM
                                recnrsernr r1
                                    inner join
                                recnrlaststation r2 ON r1.recNr = r2.recNr
                                    inner join
                                products p ON r1.productId = p.productId
                            where
                                (r2.lastStation = '".$aoi."070' or r2.lastStation = '".$aoi."071')
                            order by r2.changeDate desc 
                            Limit 1";
    
    $recent_res = mysqli_query($conn,$recent_product_query);
    $recent_product_code = mysqli_fetch_array($recent_res);
    
    if(($product_code != $recent_product_code['productCode']) && in_array($recent_product_code['productCode'],$productCodeArray)){
        
        // a production order szám meghatározásához szükséges tömbindex
        $array_index = array_search($recent_product_code['productCode'],$productCodeArray);
        $arr['array_index'] = $array_index;
        
        $arr['atallas'] = TRUE;
        $arr['atallas_kod'] = $recent_product_code['productCode'];
        
        echo json_encode($arr);
        die();
    }
?>
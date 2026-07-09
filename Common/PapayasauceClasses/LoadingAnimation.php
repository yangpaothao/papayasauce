<?php
namespace PapayasauceClasses;
/**
 * Description of newDateClass
 *
 * @author Yang Pao Thao
 */
class LoadingAnimation {
    private $cstr = "";
    
    function SetLoadscreen()
    {
        $this->cstr = "";
    }
    function GetLoadscreen()
    {
        $this->cstr = '<div class="payment-loader-container display-none" id="div_loader">';
            $this->cstr .= '<div class="loading-screen"></div>';
        $this->cstr .= '</div>';
        return($this->cstr);
    }
    
}?>

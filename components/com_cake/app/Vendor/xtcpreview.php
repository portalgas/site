<?php
class XTCPREVIEW 
{
	private $legenda = "<span class=\"h4Pdf\">Legenda: (*) Valore modificato dal referente</span>";
	
	private $css = "
				<style type='text/css'>
				p {
					font-size: 8px;
				}
				.h1Pdf {
					font-size: 22px;
					background-color:#c3d2e5;
					padding:2px;
				}
				.h2Pdf {
					font-size: 18px;
				}
				.h3Pdf {
					font-size: 15px;
				}
				.h4Pdf {
					font-size: 11px;
				}
				.h4PdfNotFound {
				    color: #FF0000;
				    font-size: 13px;
				    margin: 10px 0;
				}
			    table {
					empty-cells:show;
				}
				th {
					font-size: 18px;
					height: 17px;
					border-bottom:1px solid #555;
					text-align: left;
					background-color: #F5F5F5;
				}
				td {
					font-size: 18px;
					height: 15px;
					text-align: left;
					vertical-align: top;
					border-bottom:1px solid #ddd;
			 	    font-weight: normal;
				}
				td.trGroup, th.trGroup {
					height: 17px;
				    background-color: #E1E1E1;
				    /*border-bottom: 1px solid #DDDDDD;*/
				    font-weight: bold;
				}
				</style>";
	
	function Header() {}

    function Footer() {}

    function AddPage() {}
    
    function SetY() {}
    
    function SetFont() {
    	
    }
    
    function SetAutoPageBreak() {}

    function writeHTML($html) {
    	echo $html;
    }
    
    function writeHTMLCell($a, $b, $c, $d, $html) {
    	echo $html;
    }
    
    function lastPage() {}
    
    function Output() {}

    public function getCss() {
    	return $this->css;
    }
    
    public function getLegenda() {
    	return $this->legenda;
    }

    public function getCELLWIDTH5() { return "5";}
    public function getCELLWIDTH10() { return "10";}
    public function getCELLWIDTH20() { return "20";}
    public function getCELLWIDTH30() { return "30";}
    public function getCELLWIDTH40() { return "40";}
    public function getCELLWIDTH50() { return "50";}
    public function getCELLWIDTH60() { return "60";}
    public function getCELLWIDTH70() { return "70";}
    public function getCELLWIDTH80() { return "80";}
    public function getCELLWIDTH90() { return "90";}
    public function getCELLWIDTH100() { return "100";}
    public function getCELLWIDTH150() { return "150";}
    public function getCELLWIDTH160() { return "160";}
    public function getCELLWIDTH170() { return "170";}
    public function getCELLWIDTH180() { return "180";}
    public function getCELLWIDTH190() { return "190";}
    public function getCELLWIDTH200() { return "200";}
    public function getCELLWIDTH300() { return "300";}
}
?>
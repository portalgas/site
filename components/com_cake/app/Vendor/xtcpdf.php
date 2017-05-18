<?php
App::import('Vendor','tcpdf/tcpdf');  // http://www.tcpdf.org/doc/code/classTCPDF.html


class XTCPDF  extends TCPDF
{
	var $headerText  = '';
    var $headerColor = array(255,255,255);
    var $footerColor = array(225,225,225);
    var $footerText  = 'Copyright %d %s. All rights reserved.';
    
    private $organization;
    
    // totale righe 630 , variabili definite anche in xtcpreview
    private $CELLWIDTH5 = 5;
    private $CELLWIDTH10 = 10;
    private $CELLWIDTH20 = 20;
    private $CELLWIDTH30 = 30;
    private $CELLWIDTH40 = 40;
    private $CELLWIDTH50 = 50;
    private $CELLWIDTH60 = 60;
    private $CELLWIDTH70 = 70;
    private $CELLWIDTH80 = 80;
    private $CELLWIDTH90 = 90;
    private $CELLWIDTH100 = 100;
    private $CELLWIDTH150 = 150;
    private $CELLWIDTH160 = 160;
    private $CELLWIDTH170 = 170;
    private $CELLWIDTH180 = 180;
    private $CELLWIDTH190 = 190;
    private $CELLWIDTH200 = 200;
    private $CELLWIDTH300 = 300;

	private $legenda = "<span class=\"h5Pdf\">Legenda: (*) Valore modificato dal referente</span>";
        
	/*
	 * css supported
	 * 
	 *   font-family
	 *   font-size
	 *   font-weight
	 *   font-style
	 *   color
	 *   background-color
	 *   text-decoration
	 *   width
	 *   height
	 *   text-align
	 */
	private $css = "
				<style type='text/css'>
				p {
					font-size: 8px;
					font-weight: normal;
				}
				.h1Pdf {
					font-size: 14px;
					background-color:#c3d2e5;
					padding:2px;
				}
				.h2Pdf {
					font-size: 12px;
				}
				.h3Pdf {
					font-size: 12px;
				}
				.h4Pdf {
					font-size: 10px;
				}
				.h4PdfNotFound {
					font-size: 14px;
				}
				.h5Pdf {
					font-size: 12px;
				}
				table {
					empty-cells:show;
				}
				th {
					font-size: 12px;
					height: 17px;
					border-bottom:1px solid #555;
					text-align: left;
					background-color: #F5F5F5;
				}
				td {
					font-size: 10px;
					height: 15px;
					text-align: left;
					vertical-align: top;
					border-bottom:1px solid #ddd;
			 	    font-weight: normal;
				}
				td.trGroup, th.trGroup {
					height: 12px;
				    background-color: #E1E1E1;
				    /*border-bottom: 1px solid #DDDDDD;*/
				    font-weight: bold;
				}
				td.progressBar {
					font-size: 12px;
					height: 10px;
					border-bottom:1px solid #555;
					text-align: left;
					background-color: #0a659e;
				}				
				</style>";

	
	public function __construct($organization, $orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false);
		
		$this->organization = $organization;
		
		// set document information
		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor(Configure::read('doc_export_author'));
		$this->SetTitle(Configure::read('doc_export_title'));
		$this->SetSubject(Configure::read('doc_export_subject'));
		$this->SetKeywords(Configure::read('doc_export_keyword'));
		
		//$this->setPageOrientation($orientation='P',$autopagebreak=true,$bottommargin=10);
		
		// set default header data
		//$this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 048', PDF_HEADER_STRING);
		//$this->SetHeaderData(Configure::read('App.root').Configure::read('App.img.cake').DS.'loghi'.DS.'4.jpg',$w='144',$title=Configure::read('SOC.name'),$descri=Configure::read('SOC.name'));
		
		// set header and footer fonts
		$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		
		// set default monospaced font
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		//set margins
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->SetHeaderMargin(PDF_MARGIN_HEADER);
		$this->SetFooterMargin(PDF_MARGIN_FOOTER);
		
		$this->setPrintHeader(true);
		$this->setPrintFooter(true);
		
		//set auto page breaks
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		//set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		//set some language-dependent strings
		$this->setLanguageArray($this->l);
		
		// set font
		$this->SetFont('helvetica', 'B', 20);
		
	}
			
	function Header()
    {
    	list($r, $b, $g) = $this->headerColor;
    	$this->SetFillColor($r, $b, $g);
    	
        $this->Cell(0, 14, ucfirst($this->headerText), $border=0,$ln=1,$align='R',$fill=true,$link='',$stretch=0,$ignore_min_height=false,$calign='T',$valign='M');
        //$this->writeHTML($this->$css.$this->headerText , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');
		/*
		 * se $this->organization['Organization'] e' valorizzato inserisco il logo
		 * in OrganizationsPaysController->admin_invoice_create_pdf() non voglio il logo
		 */
		if(isset($this->organization['Organization']) && isset($this->organization['Organization']['id'])) {
			$logo = Configure::read('App.root').Configure::read('App.img.loghi').DS.$this->organization['Organization']['id'].DS.Configure::read('doc_export_logo');
			$this->Image($logo, $x=PDF_MARGIN_LEFT, $y=5); 			
		}
    }

	/*
	 * per le fatture non e' valorizzato
	 */
    function Footer()
    {
		if(!empty($this->footerText)) {
			list($r, $b, $g) = $this->footerColor;
			$this->SetFillColor($r, $b, $g);
							
			// Position at 15 mm from bottom
			$this->SetY(-15);
			$this->Cell($w=0, $h=5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), $border=0,$ln=1,$align='C',$fill=true,$link='',$stretch=0,$ignore_min_height=false,$calign='T',$valign='M');

			$text = sprintf($this->footerText, date('Y'), Configure::read('SOC.name'));
			$text .= " - Report del ".date("d/m/Y");
			$this->Cell($w=0, $h=5, $text, $border=0,$ln=1,$align='C',$fill=true,$link='',$stretch=0,$ignore_min_height=false,$calign='T',$valign='M');			
		}
    }
	 
	 public function getLegenda() {
	 	return $this->legenda;
	 }
	 
	 public function getCss() {
	 	return $this->css;
	 } 

	 public function getCELLWIDTH5() {
	 	return $this->CELLWIDTH5;
	 }
	 public function getCELLWIDTH10() {
	 	return $this->CELLWIDTH10;
	 }
	 public function getCELLWIDTH20() {
	 	return $this->CELLWIDTH20;
	 }
	 public function getCELLWIDTH30() {
	 	return $this->CELLWIDTH30;
	 }
	 public function getCELLWIDTH40() {
	 	return $this->CELLWIDTH40;
	 }
	 public function getCELLWIDTH50() {
	 	return $this->CELLWIDTH50;
	 }
	 public function getCELLWIDTH60() {
	 	return $this->CELLWIDTH60;
	 }
	 public function getCELLWIDTH70() {
	 	return $this->CELLWIDTH70;
	 }
	 public function getCELLWIDTH80() {
	 	return $this->CELLWIDTH80;
	 }
	 public function getCELLWIDTH90() {
	 	return $this->CELLWIDTH90;
	 }
	 public function getCELLWIDTH100() {
	 	return $this->CELLWIDTH100;
	 }
	 public function getCELLWIDTH150() {
	 	return $this->CELLWIDTH150;
	 }
	 public function getCELLWIDTH160() {
	 	return $this->CELLWIDTH160;
	 }
	 public function getCELLWIDTH170() {
	 	return $this->CELLWIDTH170;
	 }
	 public function getCELLWIDTH180() {
	 	return $this->CELLWIDTH180;
	 }
	 public function getCELLWIDTH190() {
	 	return $this->CELLWIDTH190;
	 }
	 public function getCELLWIDTH200() {
	 	return $this->CELLWIDTH200;
	 }
	 public function getCELLWIDTH300() {
	 	return $this->CELLWIDTH300;
	 }
}
?>
<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Nel\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Nel\Metodos\Metodos;
use Nel\Metodos\MetodosControladores;
use Nel\Modelo\Entity\AsignarModulo;
use Nel\Modelo\Entity\Cursos;
use Nel\Modelo\Entity\ConfigurarCurso;
use Nel\Modelo\Entity\Horario;
use Nel\Modelo\Entity\Dias;
use Zend\Session\Container;
use Zend\Crypt\Password\Bcrypt;
use Zend\Db\Adapter\Adapter;

class CertificadosController extends AbstractActionController
{
    public $dbAdapter;
    public function imprimirlistaAction()
    {
        $this->dbAdapter=$this->getServiceLocator()->get('Zend\Db\Adapter');

        require($_SERVER['DOCUMENT_ROOT'].$this->getRequest()->getBaseUrl().'/public/metodos/fpdf/fpdf.php');
//        $idCuerpoRifa = $this->params()->fromQuery('idCuerpoRifa');
        
        $pdf = new \FPDF();
        $pdf->AddPage();
        $y = $pdf->GetY();
        $pdf->SetFont('Arial','B',15);
//        $pdf->SetFillColor($listaColor[0]['r'],$listaColor[0]['g'],$listaColor[0]['b']);
        
        return $pdf->Output();
    }
}


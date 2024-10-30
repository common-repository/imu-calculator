<?php
/*
  IMU Calculator WordPress Plugin
  Copyright 2012 Cristian Porta (email : cristian@crx.it)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * 
 */
class IMUCalulator {

    private $coefficiente = "A";
    private $rendita = 600;
    private $detrazione_bi = false;
    private $prima_casa = false;
    private $coltivatore_diretto = false;
    private $n_figli = 0;
    private $aliquota = 0.40;
    private $quota_possesso = 100;
    private $contitolari = 1;
    private $mesi_possesso = 12;
    private $totale = 0;
    private $eta_figli = array();
    
    private $fields = array(
        'coefficiente' => '',
        'rendita' => '',
        'detrazione_bi' => '',
        'prima_casa' => '',
        'coltivatore_diretto' => '',
        'n_figli' => '',
        'aliquota' => '',
        'quota_possesso' => '',
        'contitolari' => '',
        'mesi_possesso' => '',
        'totale' => ''
    );
    private $cfx = array(
        "A" => 160,
        "C2" => 160,
        "C6" => 160,
        "C7" => 160,
        "FAC267" => 160,
        "A10" => 80,
        "C1" => 55,
        "D" => 60,
        "FAD10" => 60,
        "D5" => 80,
        "B" => 140,
        "C3" => 140,
        "C4" => 140,
        "C5" => 140,
        "TA" => 135,
        "AE" => 1
    );

    /**
     *
     * @return type 
     */
    private function calcoloIMU() {

        $dst = $this->coefficiente;
        $cfx = $this->getCfx($dst);
        $rnd = $this->rendita;
        try {
            $rnd = $rnd;
        } catch (Exception $e) {
            $rnd = 0;
        }
        $abitazionePrincipale = $this->prima_casa;

        $detrazioneBaseImponibile = false;
        if ($this->detrazione_bi) {
            $detrazioneBaseImponibile = true;
        }
        if ($dst == 'TA') {
            $detrazioneBaseImponibile = false;
        }

        $terrenoAgColtivatore = $this->coltivatore_diretto;
        $nFigli = $this->n_figli;
        try {
            $nFigli = $nFigli;
        } catch (Exception $e) {
            $nFigli = 0;
        }
        $aliquota = $this->aliquota;
        $percentualePossesso = $this->quota_possesso;
        try {
            $percentualePossesso = $percentualePossesso;
        } catch (Exception $e) {
            $percentualePossesso = 100.00;
        }
        $nCointestatari = $this->contitolari;
        try {
            $nCointestatari = $nCointestatari;
        } catch (Exception $e) {
            if ($percentualePossesso < 100)
                $nCointestatari = 2;
            $nCointestatari = 1;
        }
        $mesiPossesso = $this->mesi_possesso;

        try {
            $totaleIMU = 0;

            $totaleIMU = $rnd * 1.05;

            switch ($dst) {
                case 'TA':
                    $totaleIMU = $rnd * 1.25;
                    if ($terrenoAgColtivatore) {
                        $cfx = 110;
                    }
                    break;
                case 'AE':
                    $totaleIMU = $rnd;
                    break;
                default:
                // none
            }
            $totaleIMU = $totaleIMU * $cfx;
            if ($dst == 'TA' && $terrenoAgColtivatore) {
                $this->totale = number_format($this->calcoloIMUTA($totaleIMU, ($aliquota / 100)), 2, ',', '.');
                return;
            }
            if ($detrazioneBaseImponibile) {
                $totaleIMU = $totaleIMU / 2;
            }
            $totaleIMU = ($totaleIMU * $percentualePossesso) / 100;
            $totaleIMU = $totaleIMU * $mesiPossesso / 12;
            $totaleIMU = $totaleIMU * ($aliquota / 100);
            if ($dst == 'A' && $abitazionePrincipale) {
                if ($percentualePossesso == 100) {
                    $nCointestatari = 1;
                }

                $detrazionePrimaCasa = 200 * $mesiPossesso / 12;
                if ($nCointestatari > 1) {
                    $detrazionePrimaCasa = $detrazionePrimaCasa / $nCointestatari;
                }

                $detrazioneFigli = 0;
                if ($nFigli > 0) {
                    $detrazioneFigli = $this->detrazioneFigliACarico();
                    if ($nCointestatari > 1) {
                        $detrazioneFigli = $detrazioneFigli / $nCointestatari;
                    }
                }
                $totaleIMU = $totaleIMU - $detrazionePrimaCasa - $detrazioneFigli;
            } else if($dst == 'A' && $abitazionePrincipale == false) {
                
            }
        } catch (Exception $e) {
            $totaleIMU = 0;
        }
        if ($totaleIMU < 0) {
            $totaleIMU = 0;
        }

        $this->totale = number_format($totaleIMU, 2, ',', '.');
    }
    
    private function detrazioneFigliACarico() {
        
        $detrazione_figli = 0;
        
        foreach ($this->eta_figli as $key => $value) {
             $detrazione_figli += 50 * $value / 12;
        }
        
        return $detrazione_figli;
        
        
    }

    /**
     *
     * @param type $valore
     * @param type $aliquota
     * @return type 
     */
    private function calcoloIMUTA($valore, $aliquota) {
        if ($valore <= 6000) {
            return 0;
        } else if ($valore > 6000 && $valore <= 15500) {
            return ($valore - 6000) * $aliquota * 0.3;
        } else if ($valore > 15500 && $valore <= 25500) {
            return 21.66 + ($valore - 15500) * $aliquota * 0.5;
        } else if ($valore > 25500 && $valore <= 32000) {
            return 59.66 + ($valore - 25500) * $aliquota * 0.75;
        } else if ($valore > 32000) {
            return 96.71 + ($valore - 32000) * $aliquota;
        }
    }

    /**
     *
     * @param type $destinazione
     * @return type 
     */
    private function getCfx($destinazione) {
        return $this->cfx[$destinazione];
    }

    public function setCoefficiente($value) {
        $this->coefficiente = $value;
    }

    public function setRendita($value) {
        $this->rendita = $value;
    }

    public function setDetrazione_bi($value) {
        $this->detrazione_bi = $value;
    }

    public function setPrima_casa($value) {
        $this->prima_casa = $value;
    }

    public function setColtivatore_diretto($value) {
        $this->coltivatore_diretto = $value;
    }

    public function setN_figli($value) {
        $this->n_figli = $value;
    }

    public function setAliquota($value) {
        $this->aliquota = $value;
    }

    public function setQuota_possesso($value) {
        $this->quota_possesso = $value;
    }

    public function setContitolari($value) {
        $this->contitolari = $value;
    }

    public function setMesi_possesso($value) {
        $this->mesi_possesso = $value;
    }
    
    public function setEtaFigli($value) {
        $this->eta_figli = $value;
    }
    public function getEtaFigli() {
        return $this->eta_figli;
    }

    public function getIMU() {
        $this->calcoloIMU();
        return $this->getTotale();
    }

    public function getTotale() {
        return $this->totale;
    }

    public function getCoefficiente() {
        return$this->coefficiente;
    }

    public function getRendita() {
        return$this->rendita;
    }

    public function getDetrazione_bi() {
        return$this->detrazione_bi;
    }

    public function getPrima_casa() {
        return$this->prima_casa;
    }

    public function getColtivatore_diretto() {
        return$this->coltivatore_diretto;
    }

    public function getN_figli() {
        return$this->n_figli;
    }

    public function getAliquota() {
        return$this->aliquota;
    }

    public function getQuota_possesso() {
        return$this->quota_possesso;
    }

    public function getContitolari() {
        return$this->contitolari;
    }

    public function getMesi_possesso() {
        return$this->mesi_possesso;
    }

    public function getFields() {
        return $this->fields;
    }

    public function getField($key) {
        return $this->fields[$key];
    }

    public function existsField($key) {
        return array_key_exists($key, $this->fields);
    }

    public function setField($key, $value) {
        return $this->fields[$key] = $value;
    }
}
?>
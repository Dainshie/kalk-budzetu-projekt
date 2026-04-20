<?php

class KalkBudzetu {
    private $osoby = [];
    private $dochody = [];
    private $wydatki = [];
    
    public function dodajOsobe($id, $imie) {
        $this->osoby[$id] = [
            'id' => $id,
            'imie' => $imie,
            'bilans' => 0
        ];
    }
    
    public function dodajDochod($osobaId, $ilosc, $opis, $kat = 'Wyplata') {
        if (!isset($this->osoby[$osobaId])) {
            throw new Exception("Osoba o ID: $osobaId nie istnieje");
        }
        
        $this->dochody[] = [
            'osoba_id' => $osobaId,
            'ilosc' => $ilosc,
            'opis' => $opis,
            'kat' => $kat,
            'data' => date('Y-m-d')
        ];
        
        $this->osoby[$osobaId]['bilans'] += $ilosc;
    }

    public function dodajWydatek($osobaId, $ilosc, $opis, $kat = 'Inne') {
        if (!isset($this->osoby[$osobaId])) {
            throw new Exception("Osoba o ID: $osobaId nie istnieje");
        }
        
        if ($this->osoby[$osobaId]['bilans'] < $ilosc) {
            throw new Exception("Niewystarczające środki");
        }
        
        $this->wydatki[] = [
            'osoba_id' => $osobaId,
            'ilosc' => $ilosc,
            'opis' => $opis,
            'kat' => $kat,
            'data' => date('Y-m-d')
        ];
        
        $this->osoby[$osobaId]['bilans'] -= $ilosc;
    }


    public function bilansOsoby($osobaId) {
        return isset($this->osoby[$osobaId]) ? $this->osoby[$osobaId]['bilans'] : 0;
    }

    public function dochodyOsoby($osobaId) {
        $total = 0;
        foreach ($this->dochody as $dochod) {
            if ($dochod['osoba_id'] == $osobaId) {
                $total += $dochod['ilosc'];
            }
        }
        return $total;
    }

     public function wydatkiOsoby($osobaId) {
        $total = 0;
        foreach ($this->wydatki as $wydatek) {
            if ($wydatek['osoba_id'] == $osobaId) {
                $total += $wydatek['ilosc'];
            }
        }
        return $total;
    }
    
    public function osoby() {
        return $this->osoby;
    }


}
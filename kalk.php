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
}
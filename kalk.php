<?php
session_start();

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

    public function calyDochod() {
        return array_sum(array_column($this->dochody, 'ilosc'));
    }

    public function caleWydatki() {
        return array_sum(array_column($this->wydatki, 'ilosc'));
    }
    
    public function calyBilans() {
        return $this->calyDochod() - $this->caleWydatki();
    }
    
    public function getDochody() {
        return $this->dochody;
    }
    
    public function getWydatki() {
        return $this->wydatki;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>Kalkulator Budżetu Domowego</h1>
        </header>
    
        <div class="grid">
            <div class="card">
                <h2>Dodaj osobę</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="imie">Imię i nazwisko</label>
                        <input type="text" id="imie" name="imie">
                    </div>
                    <input type="hidden" name="action" value="dodaj_osobe">
                    <button type="submit">Dodaj osobę</button>
                </form>
            </div>


</body>
</html>
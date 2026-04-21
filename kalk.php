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
            <div class="card">
                <h2>Dodaj dochód</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="osoba_dochod">Osoba</label>
                        <select id="osoba_dochod" name="osoba_id" required>
                            <option value="">Wybierz osobę</option>
                            <?php
                                foreach ($kalkulator->osoby() as $osoba) {
                                    echo "<option value='{$osoba['id']}'>{$osoba['imie']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ilosc_dochod">Kwota (zł)</label>
                        <input type="number" id="ilosc_dochod" name="ilosc" required>
                    </div>
                    <div class="form-group">
                        <label for="opis_dochod">Opis</label>
                        <input type="text" id="opis_dochod" name="opis" required>
                    </div>
                    <div class="form-group">
                        <label for="kat_dochod">Kategoria</label>
                        <input type="text" id="kat_dochod" name="kat" value="Wyplata" placeholder="Wyplata">
                    </div>
                    <input type="hidden" name="action" value="dodaj_dochod">
                    <button type="submit">Dodaj dochód</button>
                </form>
            </div>
            <div class="card">
                <h2>Dodaj Wydatek</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="osoba_wydatek">Osoba</label>
                        <select id="osoba_wydatek" name="osoba_id" required>
                            <option value="">Wybierz osobę</option>
                            <?php
                                foreach ($kalkulator->osoby() as $osoba) {
                                    echo "<option value='{$osoba['id']}'>{$osoba['imie']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ilosc_wydatek">Kwota (zł)</label>
                        <input type="number" id="ilosc_wydatek" name="ilosc"required>
                    </div>
                    <div class="form-group">
                        <label for="opis_wydatek">Opis</label>
                        <input type="text" id="opis_wydatek" name="opis" required>
                    </div>
                    <div class="form-group">
                        <label for="kat_wydatek">Kategoria</label>
                        <input type="text" id="kat_wydatek" name="kat" value="Inne" placeholder="Inne">
                    </div>
                    <input type="hidden" name="action" value="dodaj_wydatek">
                    <button type="submit">Dodaj wydatek</button>
                </form>
            </div>
        </div>
    </div>


</body>
</html>
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


 if (!isset($_SESSION['kalkulator'])) {
    $_SESSION['kalkulator'] = new KalkBudzetu();
 }
$kalkulator = $_SESSION['kalkulator'];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'dodaj_osobe') {
                $id = count($kalkulator->osoby()) + 1;
                $imie = htmlspecialchars($_POST['imie']);
                $kalkulator->dodajOsobe($id, $imie);
                $message = "Osoba '$imie' dodana pomyślnie!";
            }
            
            elseif ($_POST['action'] == 'dodaj_dochod') {
                $osoba_id = (int)$_POST['osoba_id'];
                $ilosc = (float)$_POST['ilosc'];
                $opis = htmlspecialchars($_POST['opis']);
                $kat = htmlspecialchars($_POST['kat']) ?: 'Wyplata';
                
                $kalkulator->dodajDochod($osoba_id, $ilosc, $opis, $kat);
            }
            
            elseif ($_POST['action'] == 'dodaj_wydatek') {
                $osoba_id = (int)$_POST['osoba_id'];
                $ilosc = (float)$_POST['ilosc'];
                $opis = htmlspecialchars($_POST['opis']);
                $kat = htmlspecialchars($_POST['kat']) ?: 'Inne';
                
                $kalkulator->dodajWydatek($osoba_id, $ilosc, $opis, $kat);
            }
        }
    } catch (Exception $e) {
        $message = "Błąd: " . $e->getMessage();
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

    <div class="data-section">
            <h2>Stan Osób</h2>
            <?php if (count($kalkulator->osoby()) > 0): ?>
                <?php foreach ($kalkulator->osoby() as $osoba): ?>
                    <div class="person-card">
                        <h3><?php echo $osoba['imie']; ?></h3>
                        <div class="person-stats">
                            <div class="stat">
                                <div class="stat-label">Dochody</div>
                                <div class="stat-value"><?php echo number_format($kalkulator->dochodyOsoby($osoba['id']), 2); ?> zł</div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Wydatki</div>
                                <div class="stat-value"><?php echo number_format($kalkulator->wydatkiOsoby($osoba['id']), 2); ?> zł</div>
                            </div>
                            <div class="stat">
                                <div class="stat-label">Saldo</div>
                                <div class="stat-value"><?php echo number_format($osoba['bilans'], 2); ?> zł</div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666; text-align: center; padding: 20px;">Brak osób - dodaj osobę używając formularza powyżej</p>
            <?php endif; ?>
        </div>


</body>
</html>
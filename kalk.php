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
    <title>Kalkulator Budżetu</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        header {
            margin-bottom: 30px;
        }
        
        header h1 {
            color: #333;
            font-size: 28px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #a600ff;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 12px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #a600ff;
            background-color: #f0f7ff;
        }
        
        button {
            width: 100%;
            padding: 10px;
            background-color: #a600ff;
            color: white;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #8205a1;
        }
        
        .data-section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .data-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #a600ff;
            padding-bottom: 10px;
        }
        
        .person-card {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #a600ff;
        }
        
        .person-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .person-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }
        
        .stat {
            padding: 10px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            text-align: center;
        }
        
        .stat-label {
            color: #999;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .stat-value {
            color: #a600ff;
            font-weight: bold;
            font-size: 16px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        table th {
            background-color: #a600ff;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        
        table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        table tr:hover {
            background-color: #f9f9f9;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            
            .person-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
        
        <?php if (count($kalkulator->getDochody()) > 0): ?>
            <div class="data-section">
                <h2>Historia Dochodów</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Osoba</th>
                            <th>Kwota</th>
                            <th>Opis</th>
                            <th>Kategoria</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kalkulator->getDochody() as $dochod): ?>
                            <tr class="income-row">
                                <td><?php echo $kalkulator->osoby()[$dochod['osoba_id']]['imie']; ?></td>
                                <td><strong><?php echo number_format($dochod['ilosc'], 2); ?> zł</strong></td>
                                <td><?php echo $dochod['opis']; ?></td>
                                <td><?php echo $dochod['kat']; ?></td>
                                <td><?php echo $dochod['data']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <?php if (count($kalkulator->getWydatki()) > 0): ?>
            <div class="data-section">
                <h2>Historia Wydatków</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Osoba</th>
                            <th>Kwota</th>
                            <th>Opis</th>
                            <th>Kategoria</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kalkulator->getWydatki() as $wydatek): ?>
                            <tr class="expense-row">
                                <td><?php echo $kalkulator->osoby()[$wydatek['osoba_id']]['imie']; ?></td>
                                <td><strong><?php echo number_format($wydatek['ilosc'], 2); ?> zł</strong></td>
                                <td><?php echo $wydatek['opis']; ?></td>
                                <td><?php echo $wydatek['kat']; ?></td>
                                <td><?php echo $wydatek['data']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>


</body>
</html>
# Test 5 — po wykładzie 5 (Transakcje, współbieżność, podzapytania, widoki)

Zakres: kumulatywnie wykłady 1–5. Nacisk na ACID, izolację, podzapytania, widoki.
Suma: 9 pkt.

---

**1. (1 pkt, bardzo łatwe)**
Co oznacza litera „C” w skrócie ACID?

- A) Concurrency
- B) **Consistency (spójność)** ✅
- C) Cascade
- D) Cache

---

**2. (1 pkt, łatwe)**
Do czego służy `ROLLBACK`?

- A) Do zatwierdzenia zmian na stałe
- B) **Do cofnięcia zmian wprowadzonych w bieżącej transakcji** ✅
- C) Do usunięcia tabeli
- D) Do utworzenia punktu zapisu (`SAVEPOINT`)

---

**3. (2 pkt, średnie)**
Które z poniższych podzapytań jest **skorelowane** (correlated)?

- A) `WHERE autor_id = (SELECT autor_id FROM Autorzy WHERE nazwisko = 'Prus')`
- B) `WHERE autor_id IN (SELECT autor_id FROM Autorzy WHERE nazwisko LIKE 'M%')`
- C) **`WHERE k.rok = (SELECT MIN(rok) FROM Ksiazki WHERE autor_id = k.autor_id)` — odwołuje się do kolumny z zapytania głównego (`k.autor_id`)** ✅
- D) `FROM (SELECT * FROM Ksiazki) AS K`

---

**4. (2 pkt, trudne)**
Widok:
```sql
CREATE VIEW StudenciMlodsi AS
SELECT * FROM Studenci WHERE Wiek < 25
WITH CHECK OPTION;
```
Co się stanie przy próbie `INSERT INTO StudenciMlodsi VALUES (..., 30)`?

- A) Wstawienie się powiedzie, bo widoki nie sprawdzają warunków
- B) **Wstawienie zakończy się błędem — `WITH CHECK OPTION` blokuje wiersze niespełniające warunku `WHERE` widoku** ✅
- C) Wstawienie się powiedzie, ale wiersz nie będzie widoczny przez widok
- D) `WITH CHECK OPTION` dotyczy tylko `UPDATE`, nie `INSERT`

---

**5. (3 pkt, bardzo trudne — wnioskowanie)**
Mamy widok **bez** `WITH CHECK OPTION`:
```sql
CREATE VIEW StudenciPelnoletni AS
SELECT NrIndeksu, Imie, Nazwisko, Wiek
FROM Studenci
WHERE Wiek >= 18;
```
Student o `Wiek = 20` jest obecnie widoczny w tym widoku. Wykonujemy przez widok:
```sql
UPDATE StudenciPelnoletni SET Wiek = 15 WHERE NrIndeksu = 123;
```
Co się stanie?

- A) Błąd — nie można zmienić wartości, która przestałaby spełniać `WHERE` widoku
- B) **`UPDATE` się powiedzie (bo nie ma `WITH CHECK OPTION`, więc baza nie weryfikuje warunku po zapisie) — student naprawdę dostanie `Wiek = 15` w tabeli `Studenci`, tylko zniknie z tego widoku przy kolejnym odczycie** ✅
- C) `UPDATE` zostanie zignorowany bez efektu
- D) Zmieni się tylko wartość widoczna w widoku, nie w tabeli źródłowej

Wykład tłumaczy `WITH CHECK OPTION` tylko na przykładzie `INSERT`. Że domyślnie (bez tej klauzuli) widok nie chroni też przed „wypychającym” `UPDATE` — trzeba wywnioskować z kontrastu, nie jest to nigdzie napisane wprost.

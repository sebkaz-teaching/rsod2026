# Test 4 — po wykładzie 4 (PL/SQL, optymalizacja zapytań i indeksy)

Zakres: kumulatywnie wykłady 1–4. Nacisk na PL/SQL, EXPLAIN, indeksy.
Suma: 9 pkt.

> ⚠️ Uwaga merytoryczna (do wiadomości prowadzącego, nie dla studentów): tytuł pliku `wyklad4.qmd` w materiale źródłowym brzmi „Podstawy PL/SQL, transakcje i indeksy”, ale sama treść **nie zawiera** transakcji/ACID — to jest materiał wykładu 5. Test poniżej bazuje na faktycznej treści wykładu 4 (PL/SQL, optymalizator, EXPLAIN, indeksy), nie na jego tytule. Wart poprawienia tytułu pliku przy okazji.

---

**1. (1 pkt, bardzo łatwe)**
Jaki znak kończy wykonanie bloku PL/SQL w SQL*Plus (po `END;`)?

- A) `;;`
- B) **`/`** ✅
- C) `#`
- D) `STOP`

---

**2. (1 pkt, łatwe)**
Do czego służy indeks w bazie danych?

- A) Do przechowywania kopii zapasowej tabeli
- B) **Do szybkiego znajdowania wierszy bez konieczności skanowania całej tabeli (analogia: spis treści książki)** ✅
- C) Do automatycznego usuwania duplikatów
- D) Do szyfrowania danych w tabeli

---

**3. (2 pkt, średnie)**
Czym różni się `EXPLAIN` od `EXPLAIN ANALYZE`?

- A) `EXPLAIN` działa tylko w Oracle, `EXPLAIN ANALYZE` tylko w PostgreSQL
- B) **`EXPLAIN` pokazuje plan teoretyczny (na bazie statystyk), `EXPLAIN ANALYZE` faktycznie wykonuje zapytanie i pokazuje rzeczywisty czas** ✅
- C) `EXPLAIN ANALYZE` jest szybszą, uproszczoną wersją `EXPLAIN`
- D) Nie ma różnicy, to synonimy

---

**4. (2 pkt, trudne)**
W planie zapytania widzimy `Seq Scan on orders (cost=0.00..450.00 rows=3 width=48)`. Co to oznacza i co warto zrobić?

- A) Baza użyła indeksu — zapytanie jest już zoptymalizowane
- B) **Baza przeskanowała całą tabelę bez indeksu — warto rozważyć dodanie indeksu na kolumnie z warunku `WHERE`** ✅
- C) Zapytanie zawiera błąd składniowy
- D) `cost=0.00..450.00` oznacza czas wykonania w milisekundach

---

**5. (3 pkt, bardzo trudne — wnioskowanie)**
Tabela `Studenci` ma kolumnę `plec` (tylko 2 możliwe wartości: M/K) i kolumnę `nr_indeksu` (unikalna dla każdego studenta). Zapytania często filtrują po jednej z tych kolumn w `WHERE`. Na której kolumnie indeks przyniesie realną korzyść, a na której będzie prawie bezużyteczny — i dlaczego?

- A) Obie kolumny zyskają tak samo na indeksie
- B) **Indeks na `nr_indeksu` pomoże (wysoka selektywność — mało wierszy pasuje do warunku), indeks na `plec` niewiele da (niska selektywność — i tak trzeba przejrzeć ok. połowy tabeli)** ✅
- C) Indeks na `plec` pomoże bardziej, bo ma mniej unikalnych wartości do przeszukania
- D) Żadna z kolumn nie nadaje się do indeksowania

Wykład wspomina tylko przelotnie o „selektywności warunków” przy koszcie zapytań i podaje indeks Bitmap jako przykład dla kolumn o małej liczbie wartości — nie wyjaśnia wprost, że indeks B-Tree na kolumnie o niskiej selektywności jest mało opłacalny. To trzeba wywnioskować samodzielnie.

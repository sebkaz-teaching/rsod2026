# Test 3 — po wykładzie 3 (Normalizacja i projektowanie baz danych)

Zakres: kumulatywnie wykłady 1–3. Nacisk na normalizację, zależności funkcjonalne, ERD.
Suma: 9 pkt.

---

**1. (1 pkt, bardzo łatwe)**
Co to jest redundancja danych?

- A) Brak danych w tabeli
- B) **Powielanie tych samych informacji w wielu miejscach bazy** ✅
- C) Nadmiar indeksów w tabeli
- D) Zbyt duża liczba tabel w bazie

---

**2. (1 pkt, łatwe)**
Zapis `NrIndeksu → Imię, Nazwisko` oznacza, że:

- A) Imię i Nazwisko są kluczami obcymi
- B) **Każdemu NrIndeksu odpowiada dokładnie jedna wartość Imię i jedna wartość Nazwisko (zależność funkcjonalna)** ✅
- C) NrIndeksu zależy od Imienia i Nazwiska
- D) To zapis relacji N:M

---

**3. (2 pkt, średnie)**
Tabela `Kursy(Student, NrIndeksu, Kursy)`, gdzie kolumna `Kursy` zawiera listę: `"Bazy danych, Programowanie"`. Którą postać normalną narusza taki zapis?

- A) 2NF, bo brakuje klucza złożonego
- B) 3NF, bo istnieje zależność przechodnia
- C) **1NF — wartości w kolumnie `Kursy` nie są atomowe** ✅
- D) BCNF, bo klucz kandydujący nie jest unikalny

---

**4. (2 pkt, trudne)**
Tabela `ZAPISY(StudentID, KursID, Sala)`, klucz złożony `(StudentID, KursID)`, a `Sala` zależy wyłącznie od `KursID` (nie od całego klucza). Jaki to problem i jak go rozwiązać?

- A) Narusza 1NF — trzeba rozbić `Sala` na osobne wiersze
- B) **Narusza 2NF (zależność częściowa od części klucza) — trzeba wydzielić tabelę `KURS(KursID, Sala)`** ✅
- C) Narusza 3NF (zależność przechodnia) — trzeba wydzielić `SALA(Sala, Budynek)`
- D) To poprawny projekt, nic nie trzeba zmieniać

---

**5. (3 pkt, bardzo trudne — wnioskowanie)**
Po normalizacji `ZAPISY(StudentID, KursID, Sala)` do 2NF powstały dwie tabele: `ZAPISY(StudentID, KursID)` i `KURS(KursID, Sala)`. Chcemy z powrotem uzyskać listę: student + sala, w której ma zajęcia. Jakiego mechanizmu z wykładu 2 musimy teraz użyć, czego nie potrzebowaliśmy przed normalizacją?

- A) `GROUP BY` po `StudentID`
- B) **`JOIN` łączący `ZAPISY` z `KURS` po `KursID`** ✅
- C) Podzapytania w `WHERE`
- D) `ALTER TABLE` łączącego obie tabele w jedną

Ani wykład 2, ani wykład 3 nie mówią wprost „normalizacja oznacza więcej JOINów przy odczycie” — to koszt normalizacji, który trzeba samemu wywnioskować z rozbicia tabeli na dwie.

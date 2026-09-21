# Test 2 — po wykładzie 2 (Model relacyjny i podstawy SQL)

Zakres: wykład 1 (Od pliku do bazy danych) + wykład 2 (Model relacyjny i podstawy SQL).
5 pytań, rosnąca trudność, pytanie 5 wymaga wnioskowania (nie jest podane wprost w treści wykładu).
Suma: 9 pkt.

---

**1. (1 pkt, bardzo łatwe)**
Co to jest DBMS?

- A) Język zapytań do bazy danych
- B) **Oprogramowanie pośredniczące między użytkownikiem a bazą danych** ✅
- C) Typ pliku CSV
- D) Model relacyjny danych

---

**2. (1 pkt, łatwe)**
Co jednoznacznie identyfikuje wiersz w tabeli i nie może przyjmować wartości NULL?

- A) Klucz obcy
- B) Klucz kandydujący
- C) **Klucz główny (PRIMARY KEY)** ✅
- D) Indeks

---

**3. (2 pkt, średnie)**
Jak realizuje się relację N:M między dwiema tabelami w modelu relacyjnym?

- A) Przez dodanie kolumny NULL w jednej z tabel
- B) **Przez tabelę pośredniczącą z kluczami obcymi do obu tabel** ✅
- C) Przez połączenie obu tabel w jedną
- D) Model relacyjny nie obsługuje N:M

---

**4. (2 pkt, trudne)**
Czym różni się `INNER JOIN` od `LEFT JOIN`?

- A) INNER JOIN zwraca więcej wierszy niż LEFT JOIN
- B) LEFT JOIN zwraca tylko dopasowane rekordy, INNER JOIN wszystkie z lewej tabeli
- C) **INNER JOIN zwraca tylko dopasowane rekordy, LEFT JOIN — wszystkie z lewej tabeli, nawet bez dopasowania** ✅
- D) To synonimy, nie ma różnicy

---

**5. (3 pkt, bardzo trudne — wnioskowanie)**
Tabela `Ksiazki` ma kilka wierszy, w których `autor_id` jest `NULL` (książki bez przypisanego autora — np. antologie). Wykonujemy:

```sql
SELECT k.tytul, a.imie
FROM Ksiazki k
JOIN Autorzy a ON k.autor_id = a.autor_id;
```

Czy w wyniku pojawią się książki, które mają `autor_id = NULL`? Uzasadnienie?

- A) Tak, pojawią się z pustym `imie`
- B) **Nie — warunek `ON k.autor_id = a.autor_id` dla NULL nigdy nie jest TRUE, więc `JOIN` (czyli INNER JOIN) je pomija; trzeba by użyć `LEFT JOIN Ksiazki ... Autorzy`** ✅
- C) Tak, bo `JOIN` domyślnie zachowuje się jak `LEFT JOIN`
- D) Nastąpi błąd składniowy, bo nie można łączyć po kolumnie z NULL

Wykład nigdzie nie łączy wprost pułapki `NULL` w porównaniach z zachowaniem `JOIN` — trzeba samodzielnie zestawić obie zasady.

# RSOD 2026 — Magazyn wyposażenia „Baza Brzoza”

Scenariusz dla prowadzącego, po polsku. Obejmuje pięć istniejących wykładów. To materiał do przygotowania zajęć, zawierający również odpowiedzi; nie jest kartą do rozdania studentom w całości.

## Samodzielność kursu

Grupa zaczyna od zera. Nie zna projektu PRBA ani aplikacji PAS. Nie musi programować serwera. Wszystkie nazwy, identyfikatory i zdarzenia są fikcyjne. Kontekstem jest wydawanie zwykłego wyposażenia szkoleniowego: latarek, namiotów i radiotelefonów treningowych.

**Efekt końcowy:** student potrafi wyjaśnić, jak baza przechowuje historię wydań, wykrywa błędne dane, odpowiada na pytania magazyniera i zachowuje spójność przy równoczesnej pracy.

**Komunikat dla studentów:** „Przejmujecie ewidencję magazynu przed ćwiczeniem. Dwie osoby prowadziły osobne arkusze. Waszym zadaniem jest doprowadzić do sytuacji, w której można wiarygodnie ustalić, kto ma każdy egzemplarz sprzętu”.

## Pakiet początkowy

Pokaż na ekranie dwie kartki. W tym ćwiczeniu puste pole zwrotu oznacza niezakończone wydanie. Jeden egzemplarz może mieć najwyżej jedno niezakończone wydanie.

| Źródło | Numer wydania | Egzemplarz | Rodzaj | Zespół | Wydano | Zwrócono |
|---|---|---|---|---|---|---|
| Arkusz A | 101 | R-01 | Radiotelefon | Alfa | dzień 1, 08:00 | — |
| Arkusz B | 102 | R-01 | Radiotelefon | Bravo | dzień 1, 08:10 | — |
| Arkusz A | 103 | L-01 | Latarka | Alfa | dzień 1, 08:15 | dzień 1, 10:00 |
| Arkusz B | 104 | L-01 | Latarka | Bravo | dzień 1, 10:15 | — |

Drugie wydanie R-01 jest konfliktem. Dwa wydania L-01 stanowią poprawną historię. Nie wolno rozstrzygać konfliktu przez arbitralne skasowanie jednego wiersza: trzeba zweryfikować stan i zachować informację o korekcie.

Od wykładu 2 używaj już uzgodnionego, poprawnego stanu:

- `teams`: A — Alfa, B — Bravo, C — Charlie, D — Delta.
- `items`: R-01 i R-02 — radiotelefony; L-01 — latarka; N-01 — namiot. R-02 jest w naprawie, pozostałe są sprawne.
- `loans`: 101 — R-01 dla A, nadal wydany; 103 — L-01 dla A, zwrócony; 104 — L-01 dla B, nadal wydany; 105 — N-01 dla D, wydany dzień 1 o 07:00 i zwrócony o 08:00.
- **Dostępność teraz:** egzemplarz jest sprawny i nie ma niezakończonego wydania. Dostępny jest tylko N-01.
- **Zakres uproszczenia:** bez rezerwacji przyszłych terminów, kompletów wieloczęściowych i częściowych zwrotów. Każde wydanie dotyczy jednego egzemplarza.

Nazwy tabel i kolumn w nowych przykładach zapisuj po angielsku. PostgreSQL jest proponowanym silnikiem demonstracji SQL. Oracle PL/SQL pozostaje osobnym, wyraźnie oznaczonym pokazem na wykładzie 4. Poniższe scenariusze nie zawierają jeszcze skryptów tworzących bazę.

## Organizacja spotkania

Propozycja dla 90 minut dydaktycznych; nie stanowi informacji o planie grupy:

| Minuty | Przebieg |
|---|---|
| 0–8 | Od wykładu 2: istniejący quiz z poprzedniego materiału i krótki komentarz. Na pierwszym: diagnoza bez punktów. |
| 8–18 | Historia dnia i indywidualna prognoza wyniku. |
| 18–35 | Teoria potrzebna do rozwiązania problemu. |
| 35–45 | Zadanie A w parach, porównanie odpowiedzi. |
| 45–65 | Pokaz i zadanie B. |
| 65–80 | Dodatkowe zdarzenie oraz analiza konsekwencji. |
| 80–90 | Omówienie rozwiązania i indywidualne pytanie końcowe. |

Na krótszym wykładzie zadanie B wykonuje prowadzący z udziałem grupy. Role w parach: magazynier wyjaśniający potrzebę i analityk sprawdzający regułę; zamiana przy zadaniu B. Scenariusze uzupełniają istniejący quiz, nie zmieniają jego pytań ani punktacji.

## Wykład 1 — Któremu arkuszowi wierzyć?

Powiązanie: [Od pliku do bazy danych](../lectures/wyklad1.qmd).

**Otwarcie:** pokaż obie kartki bez komentarza. „Za pięć minut przychodzi Alfa po kolejny radiotelefon. Co możecie stwierdzić, a czego jeszcze nie wiecie?”

**Zadanie A:** wskaż rzeczywisty konflikt i poprawną historię. Oddawany wynik: zaznaczone wiersze i jednozdaniowe uzasadnienie. Oczekiwane: konflikt 101/102; brak konfliktu 103/104.

**Zadanie B:** zaproponuj trzy reguły, które powinna sprawdzać wspólna ewidencja. Oczekiwane: istniejący egzemplarz, istniejący odbiorca, brak dwóch aktywnych wydań tego samego egzemplarza. Reguła „każdy numer sprzętu występuje tylko raz w historii” jest błędna.

**Zmiana warunków:** arkusze scalamy do jednego pliku na współdzielonym dysku. Poproś o ponowną ocenę. Samo wspólne miejsce nie określa obsługi równoczesnych zapisów, walidacji ani uprawnień.

**Omówienie:** rozróżnij dane, interpretację, bazę i DBMS. Baza nie ustali samodzielnie, komu fizycznie wydano sprzęt; poprawność reguł i rzetelność danych wejściowych pozostają konieczne.

**Pytanie końcowe:** „Czy dwa wiersze z R-01 zawsze oznaczają błąd?” Odpowiedź: nie; znaczenie ma niezakończone wydanie, nie samo powtórzenie identyfikatora.

## Wykład 2 — Raport dla magazyniera

Powiązanie: [Model relacyjny i SQL](../lectures/wyklad2.qmd).

**Otwarcie:** użyj poprawnego stanu z pakietu. „Mamy cztery egzemplarze. Ile można wydać teraz?” Najpierw głosowanie, potem rozdzielenie sprawności od dostępności.

**Zadanie A:** połącz aktywne wydania z nazwami zespołów. Wynik kontrolny: R-01 — Alfa; L-01 — Bravo. Historyczny zwrot nie może pojawić się w raporcie aktywnych wydań.

**Zadanie B:** pokaż wszystkie zespoły i liczbę aktywnych wydań, także zero. Wynik: Alfa 1, Bravo 1, Charlie 0, Delta 0. W omówieniu użyj `LEFT JOIN` i liczenia identyfikatora wydania. Porównaj warunek aktywności w `ON` z warunkiem w `WHERE`, `returned_at IS NULL`: wariant z `WHERE` zgubi Deltę, która ma wyłącznie zakończone wydanie. Charlie bez jakiejkolwiek historii pozostanie w obu wynikach.

**Zmiana warunków:** ktoś próbuje dopisać wydanie dla zespołu X, którego nie ma w ewidencji. Studenci wskazują, jaką regułę naruszono. Oczekiwane: klucz obcy; sam klucz główny wydania nie wystarczy.

**Omówienie:** poprawny raport musi odpowiadać na pytanie biznesowe, nie tylko wykonywać się bez błędu. Nie wprowadzaj jeszcze podzapytań; zadanie dostępności można rozstrzygnąć na tabelkach.

**Pytanie końcowe:** „Czy sprawny radiotelefon zawsze jest dostępny?” Odpowiedź: nie, może być wydany.

## Wykład 3 — Jedna zmiana, trzy sprzeczne wersje

Powiązanie: [Normalizacja](../lectures/wyklad3.qmd).

**Otwarcie:** pokaż płaską tabelę `loan_id, item_id, item_name, team_id, team_name, issued_at, returned_at`. W różnych wierszach z `team_id=A` wpisz „Alfa” i „Alpha”. Przyjmij regułę, że zespół ma jedną aktualną nazwę; historii nazw jeszcze nie przechowujemy.

**Zadanie A:** wypisz zależności: `loan_id` wyznacza dane wydania, `item_id` wyznacza nazwę egzemplarza, `team_id` wyznacza nazwę zespołu. Oczekiwane: nazwa zespołu powinna mieć jedno miejsce aktualizacji.

**Zadanie B:** rozdziel tabelę na zespoły, egzemplarze i wydania. Odtwórz raport z wykładu 2 przez łączenie po kluczach. Kryterium: liczba zdarzeń historycznych nie zmienia się, Charlie może istnieć bez wydania.

**Zmiana warunków:** usuwamy zakończone wydanie 105 Delty w kopii danych demonstracyjnych. Czy znika informacja o istnieniu zespołu? W poprawnym projekcie nie; w płaskiej ewidencji może wystąpić anomalia usuwania.

**Omówienie:** podział nie oznacza „każda kolumna do osobnej tabeli”. Wymagane są reguły dziedziny i możliwość poprawnego odtworzenia informacji. BCNF omawiaj na osobnym małym przykładzie z wykładu, bez sztucznego komplikowania magazynu.

**Pytanie końcowe:** „Czy normalizacja zapobiegnie dwóm jednoczesnym wydaniom?” Odpowiedź: sama nie; to również problem ograniczeń i transakcji.

## Wykład 4 — Raport trwa dłużej niż wydawanie sprzętu

Powiązanie: [PL/SQL i optymalizacja](../lectures/wyklad4.qmd).

**Otwarcie:** „Historia urosła do setek tysięcy wpisów. Interesuje nas jeden egzemplarz”. Dane o czasach prezentuj jako zmierzone w przygotowanej demonstracji, nie jako obietnicę przyspieszenia.

**Zadanie A:** na dwóch dostarczonych planach tego samego zapytania zaznacz odczyt tabeli, użycie indeksu, szacunek liczby wierszy i rzeczywistą liczbę wierszy, jeśli dostępna. Oczekiwane: `cost` nie jest czasem w milisekundach; indeks nie musi być użyty.

**Zadanie B:** zaproponuj indeks dla historii jednego `item_id`, sortowanej po `issued_at`. Kandydatem jest indeks złożony zaczynający się od `item_id`. Uzasadnij go zapytaniem i zaproponuj pomiar przed/po; nie przyznawaj punktu wyłącznie za nazwę indeksu.

**Zmiana warunków:** zamiast jednego egzemplarza pobieramy prawie całą historię. Studenci przewidują, dlaczego skan tabeli może być rozsądny, i wskazują koszt utrzymania indeksu podczas zapisu.

**Osobny pokaz PL/SQL:** w Oracle przedstaw blok wyliczający liczbę aktywnych wydań i wypisujący komunikat. Uczestnicy wskazują część SQL oraz instrukcje proceduralne. Nie kopiują tego bloku do SQLite ani PostgreSQL. Gdy Oracle nie jest dostępny, jest to analiza kodu, a nie deklarowany pokaz wykonania.

**Pytanie końcowe:** „Jaki dowód przekona Cię, że indeks pomógł?” Odpowiedź: porównywalny pomiar i plan na właściwym zbiorze danych, z uwzględnieniem kosztu zapisów.

## Wykład 5 — Dwie osoby przy jednym okienku

Powiązanie: [Transakcje, podzapytania i widoki](../lectures/wyklad5.qmd).

**Otwarcie:** N-01 jest ostatnim dostępnym namiotem. Dwa stanowiska najpierw odczytują jego dostępność, a następnie próbują utworzyć dwa różne wydania. Rozpisz kolejność A-odczyt, B-odczyt, A-zapis, B-zapis.

**Zadanie A:** wskaż, dlaczego wcześniejszy `SELECT` nie stanowi gwarancji. Dla PostgreSQL omów ograniczenie unikalności `item_id` wśród niezakończonych wydań, np. indeksem częściowym, oraz transakcję i obsługę konfliktu. Zwykłe objęcie odczytu i zapisu transakcją bez właściwego mechanizmu ochrony nie rozwiązuje automatycznie problemu.

**Zadanie B:** zaprojektuj raport dostępnych egzemplarzy: sprawne oraz bez aktywnego wydania. Użyj `NOT EXISTS`; wynik na pakiecie początkowym: N-01. Następnie opakuj odczyt w widok i wyjaśnij jego przeznaczenie.

**Zmiana warunków:** interfejs nie dostał potwierdzenia zapisu. „Czy można bez sprawdzenia utworzyć kolejne wydanie?” Oczekiwane: wynik operacji jest nieznany klientowi; trzeba sprawdzić stan/identyfikator operacji. To pytanie do dyskusji, implementacja mechanizmu ponowień jest poza zakresem.

**Omówienie:** nazwij wszystkie własności ACID i przypisz je do przykładu. Wyjaśnij, że widok upraszcza odczyt, ale jego samo istnienie nie odbiera użytkownikowi dostępu do tabel źródłowych.

**Pytanie końcowe:** „Co pokażemy drugiemu magazynierowi?” Odpowiedź: jasny konflikt i aktualny stan, bez drugiego aktywnego wydania.

## Ocena i przygotowanie prowadzącego

Proponowana ocena formatywna każdego zadania: 0–2 pkt za wynik, 0–1 za uzasadnienie, 0–1 za wskazany przypadek brzegowy. Każdy student osobno odpowiada na pytanie końcowe. Ta skala nie zmienia zasad zaliczenia ani ocen w istniejącym backendzie.

Przed zajęciami przygotuj slajd z kartkami, poprawny stan danych, a przed wykładem 4 także rzeczywiste plany zapytań z jednego środowiska. Przed wykładem 5 sprawdź demonstrację w dwóch sesjach bazy. Student wracający po nieobecności dostaje poprawny stan początkowy, nie musi odtwarzać cudzych błędów ani znać innego kursu.

# Archived opening quiz — lecture 3

## ✅ Sprawdź się z wykładu 2

<div id="rsod-quiz"></div>
<script src="../assets/js/quiz-engine.js"></script>
<script>
CourseQuiz.mount(document.getElementById('rsod-quiz'), {
  course: "rsod2026",
  lecture: "wyklad3",
  title: "Wykład 2: Model relacyjny i podstawy SQL",
  submitUrl: "https://sebastianzajac.pl/rsod-quiz/submit.php",
  questions: [
    {
      id: "q1", points: 1, diff: "bardzo łatwe",
      text: "Co robi klauzula FOREIGN KEY w definicji tabeli?",
      options: [
        {v:"A", t:"Tworzy nową tabelę"},
        {v:"B", t:"Tworzy powiązanie z inną tabelą i wymusza, by wartość istniała w tabeli nadrzędnej"},
        {v:"C", t:"Sortuje dane w tabeli"},
        {v:"D", t:"Usuwa duplikaty wierszy"}
      ]
    },
    {
      id: "q2", points: 1, diff: "łatwe",
      text: "Co jednoznacznie identyfikuje wiersz w tabeli i nie może przyjmować wartości NULL?",
      options: [
        {v:"A", t:"Klucz obcy"},
        {v:"B", t:"Klucz kandydujący"},
        {v:"C", t:"Klucz główny (PRIMARY KEY)"},
        {v:"D", t:"Indeks"}
      ]
    },
    {
      id: "q3", points: 2, diff: "średnie",
      text: "Jak realizuje się relację N:M między dwiema tabelami w modelu relacyjnym?",
      options: [
        {v:"A", t:"Przez dodanie kolumny NULL w jednej z tabel"},
        {v:"B", t:"Przez tabelę pośredniczącą z kluczami obcymi do obu tabel"},
        {v:"C", t:"Przez połączenie obu tabel w jedną"},
        {v:"D", t:"Model relacyjny nie obsługuje N:M"}
      ]
    },
    {
      id: "q4", points: 2, diff: "trudne",
      text: "Czym różni się INNER JOIN od LEFT JOIN?",
      options: [
        {v:"A", t:"INNER JOIN zwraca więcej wierszy niż LEFT JOIN"},
        {v:"B", t:"LEFT JOIN zwraca tylko dopasowane rekordy, INNER JOIN wszystkie z lewej tabeli"},
        {v:"C", t:"INNER JOIN zwraca tylko dopasowane rekordy, LEFT JOIN — wszystkie z lewej tabeli, nawet bez dopasowania"},
        {v:"D", t:"To synonimy, nie ma różnicy"}
      ]
    },
    {
      id: "q5", points: 3, diff: "wnioskowanie",
      text: "Tabela Ksiazki ma wiersze, w których autor_id jest NULL (książki bez przypisanego autora). Czy poniższe zapytanie je zwróci?",
      code: "SELECT k.tytul, a.imie\nFROM Ksiazki k\nJOIN Autorzy a ON k.autor_id = a.autor_id;",
      options: [
        {v:"A", t:"Tak, pojawią się z pustym imie"},
        {v:"B", t:"Nie — warunek ON k.autor_id = a.autor_id dla NULL nigdy nie jest TRUE, JOIN (czyli INNER JOIN) je pomija; trzeba LEFT JOIN"},
        {v:"C", t:"Tak, bo JOIN domyślnie zachowuje się jak LEFT JOIN"},
        {v:"D", t:"Nastąpi błąd składniowy"}
      ]
    }
  ]
});
</script>

---


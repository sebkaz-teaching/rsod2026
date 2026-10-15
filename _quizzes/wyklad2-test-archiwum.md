# Archiwum testu przed wykładem 2

## ✅ Sprawdź się z wykładu 1

<div id="rsod-quiz"></div>
<script src="../assets/js/quiz-engine.js"></script>
<script>
CourseQuiz.mount(document.getElementById('rsod-quiz'), {
  course: "rsod2026",
  lecture: "wyklad2",
  title: "Wykład 1: Od pliku do bazy danych",
  submitUrl: "https://sebastianzajac.pl/rsod-quiz/submit.php",
  questions: [
    {
      id: "q1", points: 1, diff: "bardzo łatwe",
      text: "Czym różni się dana od informacji?",
      options: [
        {v:"A", t:"To synonimy, nie ma różnicy"},
        {v:"B", t:"Informacja to dana zapisana w pliku CSV"},
        {v:"C", t:"Informacja powstaje, gdy dana zostanie zinterpretowana w określonym kontekście"},
        {v:"D", t:"Dana zawsze dotyczy liczb, informacja — tekstu"}
      ]
    },
    {
      id: "q2", points: 1, diff: "łatwe",
      text: "Do której kategorii ustrukturyzowania danych należy plik JSON?",
      options: [
        {v:"A", t:"Ustrukturyzowane"},
        {v:"B", t:"Półustrukturyzowane"},
        {v:"C", t:"Nieustrukturyzowane"},
        {v:"D", t:"JSON nie jest formą przechowywania danych"}
      ]
    },
    {
      id: "q3", points: 2, diff: "średnie",
      text: "W architekturze ANSI/SPARC, który poziom odpowiada za sposób, w jaki konkretny użytkownik widzi dane (np. widok „Studenci z kierunku Informatyka”)?",
      options: [
        {v:"A", t:"Wewnętrzny (fizyczny)"},
        {v:"B", t:"Logiczny (koncepcyjny)"},
        {v:"C", t:"Zewnętrzny"},
        {v:"D", t:"Sieciowy"}
      ]
    },
    {
      id: "q4", points: 2, diff: "trudne",
      text: "Który typ bazy danych modeluje relacje jako węzły i krawędzie, a nie jako tabele?",
      options: [
        {v:"A", t:"Relacyjna"},
        {v:"B", t:"Dokumentowa (NoSQL)"},
        {v:"C", t:"Grafowa"},
        {v:"D", t:"Hierarchiczna"}
      ]
    },
    {
      id: "q5", points: 3, diff: "wnioskowanie",
      text: "Administrator zmienia fizyczny sposób przechowywania danych na dysku (np. format plików, nowe indeksy), nie zmieniając struktury logicznej tabel. Zgodnie z architekturą ANSI/SPARC, czy aplikacje korzystające z bazy muszą zostać przepisane?",
      options: [
        {v:"A", t:"Tak, każda zmiana fizyczna wymaga przepisania aplikacji"},
        {v:"B", t:"Nie — dzięki niezależności fizycznej danych zmiana warstwy wewnętrznej nie wpływa na warstwę logiczną ani na aplikacje"},
        {v:"C", t:"Tak, ale tylko jeśli baza jest typu NoSQL"},
        {v:"D", t:"Nie da się tego ocenić bez znajomości konkretnego DBMS"}
      ]
    }
  ]
});
</script>

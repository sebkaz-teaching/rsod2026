import json,sqlite3,subprocess,tempfile,time,urllib.request,urllib.error,shutil,uuid
from pathlib import Path
src=Path(__file__).resolve().parents[1]
with tempfile.TemporaryDirectory(prefix='prba-http-') as tmp:
 p=Path(tmp)
 for name in ('db.php','schema.sql','results.php','assignment-submit.php','submit.php'):shutil.copy(src/name,p/name)
 (p/'config.php').write_text("<?php return ['db_path'=>__DIR__.'/test.db','allowed_origin'=>'https://sebkaz-teaching.github.io','answer_keys'=>['prba2026'=>['wyklad2'=>['q1'=>['correct'=>'B','points'=>1]]]]];")
 server=subprocess.Popen(['php','-S','127.0.0.1:18763','-t',tmp],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
 def req(path,body=None,method=None,origin='https://sebkaz-teaching.github.io'):
  data=json.dumps(body).encode() if body is not None else None
  r=urllib.request.Request('http://127.0.0.1:18763/'+path,data=data,method=method,headers={'Content-Type':'application/json','Origin':origin})
  try:
   with urllib.request.urlopen(r,timeout=5) as v:return v.status,v.read().decode(),v.headers
  except urllib.error.HTTPError as e:return e.code,e.read().decode(),e.headers
 try:
  for _ in range(50):
   try:req('results.php');break
   except urllib.error.URLError:time.sleep(.1)
  else:raise RuntimeError('PHP server unavailable')
  body={'request_id':str(uuid.uuid4()),'course':'prba2026','lecture':'wyklad1','email':'one@example.test','partner_email':'two@example.test','authors':'<script>alert(1)</script>','answers':{k:'Pytanie <script>alert(1)</script>' for k in ('question1','question2','question3','concepts','rules','case')}}
  status,text,_=req('assignment-submit.php',body);assert status==200,(status,text);receipt=json.loads(text)
  assert req('assignment-submit.php',body)[1]==text
  changed={**body,'authors':'Different'};assert req('assignment-submit.php',changed)[0]==409
  changed['request_id']=str(uuid.uuid4());assert req('assignment-submit.php',changed)[0]==200
  assert req('assignment-submit.php',body,origin='https://evil.example')[0]==403
  for field,value in [('email','bad'),('authors',''),('course','pas2026'),('lecture','unknown')]:
   bad={**body,field:value};assert req('assignment-submit.php',bad)[0]==422,field
  bad={**body,'answers':{**body['answers'],'question1':' '}};assert req('assignment-submit.php',bad)[0]==422
  bad={**body,'authors':'x'*70000};assert req('assignment-submit.php',bad)[0]==413
  assert req('assignment-submit.php',method='OPTIONS')[0]==204
  assert req('assignment-submit.php')[0]==405
  assert req('submit.php',{'course':'prba2026','lecture':'wyklad2','email':'quiz@example.test','answers':{'q1':'B'}})[0]==200
  db=sqlite3.connect(p/'test.db');assert db.execute('SELECT count(*) FROM assignment_submissions').fetchone()[0]==2
  assert db.execute('SELECT score FROM submissions').fetchone()[0]==1
  page=req('results.php?view=assignments&course=prba2026&lecture=wyklad1')[1]
  assert '<script>alert(1)</script>' not in page and '&lt;script&gt;' in page
  assert 'lecture=wyklad1' not in page.split('Wykład:')[1].split('wszystkie')[0]
  print('PASS: actual HTTP save, retry deduplication, changed revision, validation, limits, CORS, escaped panel, filters, and existing quiz scoring.')
 finally:server.terminate();server.wait()

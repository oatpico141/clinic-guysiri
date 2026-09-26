const branches = [
  {id:'maruay1',name:'มารวย 1',area:'หทัยราษฎร์ / ตลาดมารวย',province:'ปทุมธานี',lat:13.9496,lon:100.6905,bma:false,watch:['หทัยราษฎร์','ถนนเชื่อมลำลูกกา','จุดต่ำหน้าตลาด'],maps:'กายสิริ คลินิกกายภาพบำบัด ตลาดมารวย'},
  {id:'maruay2',name:'มารวย 2',area:'หทัยราษฎร์ / ตลาดมารวย',province:'ปทุมธานี',lat:13.9499,lon:100.6910,bma:false,watch:['หทัยราษฎร์','ถนนเชื่อมลำลูกกา','จุดต่ำหน้าตลาด'],maps:'กายสิริ คลินิกกายภาพบำบัด ตลาดมารวย'},
  {id:'watcharaphon',name:'วัชรพล',area:'วัชรพล / รามอินทรา',province:'กรุงเทพฯ',lat:13.8758,lon:100.6412,bma:true,watch:['ถนนวัชรพล','รามอินทรา','สุขาภิบาล 5'],maps:'กายสิริ คลินิกกายภาพบำบัด วัชรพล'},
  {id:'salaya',name:'ศาลายา',area:'พุทธมณฑลสาย 4 / ศาลายา',province:'นครปฐม',lat:13.8016,lon:100.3236,bma:false,watch:['พุทธมณฑลสาย 4','ศาลายา','บรมราชชนนี'],maps:'กายสิริ คลินิกกายภาพบำบัด ศาลายา'},
  {id:'bangna',name:'บางนา',area:'ลาซาล / บางนา',province:'กรุงเทพฯ',lat:13.6608,lon:100.6358,bma:true,watch:['แบริ่งตัดใหม่–ลาซาล','ศรีนครินทร์','บางนา–ตราด'],maps:'กายสิริ คลินิกกายภาพบำบัด บางนา ลาซาล อเวนิว'},
  {id:'bangkae',name:'บางแค',area:'เพชรเกษม / บางแค',province:'กรุงเทพฯ',lat:13.7055,lon:100.3978,bma:true,watch:['เพชรเกษม','กัลปพฤกษ์','ราชพฤกษ์'],maps:'กายสิริ คลินิกกายภาพบำบัด บางแค'},
  {id:'lamlukka1',name:'ลำลูกกา 1',area:'ลำลูกกา / บึงคำพร้อย',province:'ปทุมธานี',lat:13.9328,lon:100.7472,bma:false,watch:['ลำลูกกา','ไสวประชาราษฎร์','ทางเชื่อมคลอง'],maps:'กายสิริ คลินิกกายภาพบำบัด ลำลูกกา'},
  {id:'lamlukka2',name:'ลำลูกกา 2',area:'ลำลูกกา / ปทุมธานี',province:'ปทุมธานี',lat:13.9750,lon:100.7800,bma:false,watch:['ลำลูกกา','ไสวประชาราษฎร์','ทางเชื่อมคลอง'],maps:'กายสิริ คลินิกกายภาพบำบัด ลำลูกกา'}
];

const state = { weather:{}, bma:null, selected:'bangna', sortRisk:false, mode:new URLSearchParams(location.search).get('mode')==='admin'?'admin':'patient', map:null, markers:{} };
const $ = s => document.querySelector(s); const $$ = s => [...document.querySelectorAll(s)];
const esc = s => encodeURIComponent(s);

function routeLink(b){ return `https://www.google.com/maps/search/?api=1&query=${esc(b.maps)}`; }
function officialLink(b){ return b.bma ? 'https://floodbangkok.bangkok.go.th/' : 'https://disaster.gistda.or.th/'; }
function wxText(code){ if([95,96,99].includes(code)) return 'พายุฝนฟ้าคะนอง'; if([80,81,82].includes(code)) return 'ฝนเป็นช่วง'; if([61,63,65].includes(code)) return 'ฝนตก'; if([51,53,55].includes(code)) return 'ฝนปรอย'; if([45,48].includes(code)) return 'หมอก'; if([1,2,3].includes(code)) return 'มีเมฆ'; if(code===0) return 'ท้องฟ้าโปร่ง'; return 'ไม่ทราบ'; }
function riskFrom(w){ if(!w || w.error) return {level:'mid',score:1,label:'รอตรวจสอบ',advice:'ข้อมูลฝนไม่พร้อม'}; const maxProb=Math.max(...w.hours.map(x=>x.prob),w.prob||0); if(w.rain>=8 || maxProb>=85 || [95,96,99].includes(w.code)) return {level:'high',score:3,label:'เฝ้าระวังสูง',advice:'มีโอกาสฝนหนัก ควรเช็กถนนก่อนออก'}; if(w.rain>=2 || maxProb>=55 || [61,63,65,80,81,82].includes(w.code)) return {level:'mid',score:2,label:'เฝ้าระวัง',advice:'ควรเผื่อเวลาและเช็กจุดน้ำขัง'}; return {level:'low',score:1,label:'ฝนต่ำ',advice:'ฝนรอบสาขาอยู่ในระดับต่ำ'}; }
function fmtTime(iso){ try{return new Intl.DateTimeFormat('th-TH',{hour:'2-digit',minute:'2-digit',timeZone:'Asia/Bangkok'}).format(new Date(iso));}catch{return '–';} }
function showToast(msg){ const t=$('#toast'); t.textContent=msg; t.classList.add('show'); clearTimeout(showToast.t); showToast.t=setTimeout(()=>t.classList.remove('show'),2200); }

async function loadWeather(b){
  try{
    const url=`https://api.open-meteo.com/v1/forecast?latitude=${b.lat}&longitude=${b.lon}&current=precipitation,rain,showers,weather_code&hourly=precipitation_probability,precipitation,weather_code&timezone=Asia%2FBangkok&forecast_days=1`;
    const r=await fetch(url,{cache:'no-store'}); if(!r.ok) throw new Error('weather'); const d=await r.json();
    const c=d.current||{}; const currentHour=(c.time||'').slice(0,13)+':00'; let idx=(d.hourly?.time||[]).findIndex(t=>t===currentHour); if(idx<0) idx=0;
    const hours=[]; for(let i=idx;i<Math.min(idx+4,d.hourly.time.length);i++){hours.push({time:d.hourly.time[i],prob:Number(d.hourly.precipitation_probability[i]||0),rain:Number(d.hourly.precipitation[i]||0),code:Number(d.hourly.weather_code[i]||0)});}
    state.weather[b.id]={rain:Number(c.precipitation||0),prob:Number(d.hourly?.precipitation_probability?.[idx]||0),code:Number(c.weather_code||0),hours,updated:c.time||new Date().toISOString(),error:false};
  }catch(e){ state.weather[b.id]={error:true,hours:[]}; }
}

async function loadBma(){
  try{ const r=await fetch('/api/bma',{cache:'no-store'}); if(!r.ok) throw new Error(); state.bma=await r.json(); }
  catch(e){ state.bma={ok:false}; }
}

function renderMode(){
  $('#patientView').hidden=state.mode!=='patient'; $('#adminView').hidden=state.mode!=='admin';
  $$('.mode-btn').forEach(b=>b.classList.toggle('active',b.dataset.mode===state.mode));
  const u=new URL(location.href); if(state.mode==='admin')u.searchParams.set('mode','admin'); else u.searchParams.delete('mode'); history.replaceState({},'',u);
  if(state.mode==='admin') renderAdmin();
}

function renderSummary(){
  const risks=branches.map(b=>riskFrom(state.weather[b.id])); const watch=risks.filter(r=>r.score>=2).length; const high=risks.filter(r=>r.score===3).length; const okWeather=branches.filter(b=>state.weather[b.id]&&!state.weather[b.id].error).length;
  $('#sumRainRisk').textContent=watch; $('#sumDataHealth').textContent=`${okWeather}/8`; $('#adminHigh').textContent=high; $('#adminRaining').textContent=branches.filter(b=>(state.weather[b.id]?.rain||0)>0).length; $('#adminWeatherOk').textContent=okWeather;
  const top=high?'high':watch?'mid':'low'; const orb=$('#heroStatus .status-orb'); orb.className='status-orb '+top; orb.innerHTML=top==='high'?'!':top==='mid'?'•':'✓';
  $('#heroStatus strong').textContent=high?`${high} สาขามีฝนเฝ้าระวังสูง`:watch?`${watch} สาขาควรเฝ้าระวังฝน`:'ฝนรอบสาขาอยู่ในระดับต่ำ';
  $('#heroStatus span').textContent='สถานะน้ำบนถนนยังต้องยืนยันจากระบบทางการ';
  if(state.bma?.ok){ const c=state.bma.counts||{}; $('#sumBma').textContent=Number.isFinite(c.flood)?c.flood:'–'; $('#bmaText').textContent=`น้ำท่วม ${c.flood ?? '–'} จุด • ขังเล็กน้อย ${c.minor ?? '–'} จุด • ปกติ ${c.normal ?? '–'} จุด${state.bma.sourceTime?` • ${state.bma.sourceTime}`:''}`; $('#adminBmaState').textContent='ออนไลน์'; }
  else { $('#sumBma').textContent='เช็ก'; $('#bmaText').textContent='ดึงข้อมูลสรุปอัตโนมัติไม่ได้ — กดเปิดระบบทางการเพื่อยืนยัน'; $('#adminBmaState').textContent='ลิงก์'; }
}

function renderPicker(){ const sel=$('#branchSelect'); sel.innerHTML=branches.map(b=>`<option value="${b.id}">สาขา${b.name} — ${b.area}</option>`).join(''); sel.value=state.selected; }

function renderFocus(){
  const b=branches.find(x=>x.id===state.selected)||branches[0]; const w=state.weather[b.id]; const r=riskFrom(w);
  $('#branchFocus').innerHTML=`
    <div class="focus-head"><div class="focus-title"><span class="kicker">สาขาที่เลือก</span><h2>สาขา${b.name}</h2><p>${b.area} • ${b.province}</p></div><span class="risk-badge ${r.level}">${r.label}</span></div>
    <div class="focus-status">
      <div class="status-box"><small>ฝนขณะนี้</small><strong>${w&&!w.error?w.rain.toFixed(1)+' มม.':'–'}</strong><em>${w&&!w.error?wxText(w.code):'ข้อมูลไม่พร้อม'}</em></div>
      <div class="status-box"><small>น้ำบนถนน</small><strong>ต้องยืนยัน</strong><em>${b.bma?'ตรวจจากระบบ กทม.':'ตรวจจาก GISTDA / สภาพพื้นที่'}</em></div>
      <div class="status-box"><small>โอกาสฝนชั่วโมงนี้</small><strong>${w&&!w.error?w.prob+'%':'–'}</strong><em>${r.advice}</em></div>
      <div class="status-box"><small>แหล่งอ้างอิงหลัก</small><strong>${b.bma?'กทม.':'GISTDA'}</strong><em>เปิดตรวจสอบก่อนเดินทาง</em></div>
    </div>
    <div class="watch-list">${b.watch.map(x=>`<span class="watch-chip">${x}</span>`).join('')}</div>
    <div class="focus-actions"><a class="action-link primary" href="${officialLink(b)}" target="_blank" rel="noopener">🌊 เช็กน้ำท่วม</a><a class="action-link" href="${routeLink(b)}" target="_blank" rel="noopener">🗺️ เปิด Maps</a><a class="action-link" href="tel:021244666">☎️ โทรคลินิก</a><button class="copy-btn" onclick="copyBranch('${b.id}')">คัดลอกสถานะส่ง LINE / แชท</button></div>
    <div class="source-line"><i></i> ฝนอัปเดต ${w&&!w.error?fmtTime(w.updated):'–'} • เว็บไม่ใช้ “ฝนตก” แทนคำว่า “น้ำท่วม”</div>`;
  renderHourly(b);
}

function renderHourly(b){ const w=state.weather[b.id]; const el=$('#hourlyForecast'); if(!w||w.error||!w.hours.length){el.innerHTML='<div class="note soft">ข้อมูลรายชั่วโมงยังไม่พร้อม</div>';return;} el.innerHTML=w.hours.map(h=>{const l=h.prob>=80?'high':h.prob>=55?'mid':'';return `<div class="hour ${l}"><b>${fmtTime(h.time)}</b><strong>${h.prob}%</strong><span>${h.rain.toFixed(1)} มม.</span></div>`}).join(''); }

function renderCards(){
  let list=[...branches]; if(state.sortRisk) list.sort((a,b)=>riskFrom(state.weather[b.id]).score-riskFrom(state.weather[a.id]).score);
  $('#branchCards').innerHTML=list.map(b=>{const w=state.weather[b.id],r=riskFrom(w);return `<article class="branch-card" onclick="selectBranch('${b.id}')"><div class="branch-card-head"><div><h3>สาขา${b.name}</h3><p>${b.area}</p></div><i class="mini-risk ${r.level}" title="${r.label}"></i></div><div class="branch-weather"><strong>${w&&!w.error?w.prob+'%':'–'}</strong><span>โอกาสฝนชั่วโมงนี้</span></div><div class="branch-meta">${r.label} • จุดเฝ้าระวัง ${b.watch[0]}${b.watch[1]?' / '+b.watch[1]:''}</div></article>`}).join('');
}

function renderAdmin(){
  const q=($('#adminSearch')?.value||'').toLowerCase(); const filter=$('#adminFilter')?.value||'all'; let list=[...branches].sort((a,b)=>riskFrom(state.weather[b.id]).score-riskFrom(state.weather[a.id]).score);
  list=list.filter(b=>{const r=riskFrom(state.weather[b.id]); const hay=[b.name,b.area,b.province,...b.watch].join(' ').toLowerCase(); return (!q||hay.includes(q))&&(filter==='all'||r.level===filter);});
  $('#adminRows').innerHTML=list.map(b=>{const w=state.weather[b.id],r=riskFrom(w); const max=w&&!w.error?Math.max(...w.hours.map(h=>h.prob),w.prob):null; return `<tr><td><b>สาขา${b.name}</b><div class="td-muted">${b.area}</div></td><td>${w&&!w.error?w.rain.toFixed(1)+' มม.':'–'}</td><td>${w&&!w.error?w.prob+'%':'–'}</td><td>${max!==null?max+'%':'–'}</td><td><span class="row-risk ${r.level}"><i></i>${r.label}</span></td><td class="td-muted">${b.watch.join(' • ')}</td><td><div class="table-tools"><a href="${officialLink(b)}" target="_blank" rel="noopener">น้ำ</a><a href="${routeLink(b)}" target="_blank" rel="noopener">Maps</a><button onclick="copyBranch('${b.id}')">คัดลอก</button></div></td></tr>`}).join('');
}

function renderMap(){
  if(!window.L){ $('#map').innerHTML='<div class="map-fallback">แผนที่โหลดไม่ได้ แต่ข้อมูลสาขายังใช้งานได้</div>'; return; }
  if(!state.map){ state.map=L.map('map',{scrollWheelZoom:false,zoomControl:true}).setView([13.82,100.57],10); L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(state.map); }
  branches.forEach(b=>{const r=riskFrom(state.weather[b.id]); if(state.markers[b.id]) state.map.removeLayer(state.markers[b.id]); const icon=L.divIcon({className:'',html:`<div class="map-pin ${r.level}"></div>`,iconSize:[28,28],iconAnchor:[14,28]}); const m=L.marker([b.lat,b.lon],{icon}).addTo(state.map).bindPopup(`<div class="map-popup"><b>สาขา${b.name}</b><span>${b.area}</span><span>${r.label}</span></div>`); m.on('click',()=>selectBranch(b.id,false)); state.markers[b.id]=m; });
  setTimeout(()=>state.map.invalidateSize(),100);
}

function selectBranch(id,scroll=true){ state.selected=id; $('#branchSelect').value=id; renderFocus(); if(state.markers[id]){state.map?.setView(state.markers[id].getLatLng(),12,{animate:true});state.markers[id].openPopup();} if(scroll) $('#branch-picker').scrollIntoView({behavior:'smooth',block:'start'}); }

async function copyBranch(id){ const b=branches.find(x=>x.id===id); const w=state.weather[id],r=riskFrom(w); const max=w&&!w.error&&w.hours.length?Math.max(...w.hours.map(h=>h.prob)):null; const text=`อัปเดตเส้นทาง กายสิริ สาขา${b.name}
• ฝนรอบสาขา: ${r.label}${w&&!w.error?` (โอกาสฝนขณะนี้ ${w.prob}%, 4 ชม.สูงสุด ${max}%)`:''}
• จุดเฝ้าระวัง: ${b.watch.join(', ')}
• สถานะน้ำบนถนน: กรุณายืนยันจากระบบทางการก่อนเดินทาง
ตรวจน้ำท่วม: ${officialLink(b)}
เปิดเส้นทาง: ${routeLink(b)}
โทร 02-124-4666`;
  try{await navigator.clipboard.writeText(text);showToast('คัดลอกข้อความแล้ว');}catch{showToast('คัดลอกไม่สำเร็จ');}
}
window.copyBranch=copyBranch; window.selectBranch=selectBranch;

async function copyAll(){ const lines=branches.map(b=>{const w=state.weather[b.id],r=riskFrom(w);return `• ${b.name}: ${r.label}${w&&!w.error?` | ฝน ${w.rain.toFixed(1)} มม. | ${w.prob}%`:''}`}); const text=`GUYSIRI Route Status
${new Intl.DateTimeFormat('th-TH',{dateStyle:'medium',timeStyle:'short',timeZone:'Asia/Bangkok'}).format(new Date())}
${lines.join('\n')}

หมายเหตุ: ฝนไม่ใช่การยืนยันน้ำท่วม ตรวจถนนจากแหล่งทางการก่อนแจ้งคนไข้`; try{await navigator.clipboard.writeText(text);showToast('คัดลอกสรุปทุกสาขาแล้ว');}catch{showToast('คัดลอกไม่สำเร็จ');} }

function bind(){
  $$('.mode-btn').forEach(b=>b.addEventListener('click',()=>{state.mode=b.dataset.mode;renderMode();}));
  $('#refreshBtn').addEventListener('click',()=>loadAll(true)); $('#branchSelect').addEventListener('change',e=>selectBranch(e.target.value,false)); $('#sortRiskBtn').addEventListener('click',()=>{state.sortRisk=!state.sortRisk;$('#sortRiskBtn').textContent=state.sortRisk?'เรียงตามชื่อสาขา':'เรียงตามความเสี่ยง';renderCards();});
  $('#adminSearch').addEventListener('input',renderAdmin); $('#adminFilter').addEventListener('change',renderAdmin); $('#copyAllBtn').addEventListener('click',copyAll);
}

async function loadAll(manual=false){
  if(manual){$('#updatedText').textContent='กำลังรีเฟรชข้อมูล…';$('#refreshBtn').disabled=true;}
  await Promise.all([...branches.map(loadWeather),loadBma()]);
  renderSummary(); renderPicker(); renderFocus(); renderCards(); renderAdmin(); renderMap(); renderMode();
  $('#updatedText').textContent='อัปเดตล่าสุด '+new Intl.DateTimeFormat('th-TH',{dateStyle:'medium',timeStyle:'short',timeZone:'Asia/Bangkok'}).format(new Date())+' • รีเฟรชทุก 5 นาที'; $('#refreshBtn').disabled=false;
  if(manual)showToast('อัปเดตข้อมูลแล้ว');
}

bind(); loadAll(); setInterval(()=>loadAll(false),300000);
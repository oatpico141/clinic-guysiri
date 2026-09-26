export default async function handler(req, res) {
  try {
    const response = await fetch('https://floodbangkok.bangkok.go.th/', { headers: { 'user-agent': 'GUYSIRI-Route-Status/1.0' } });
    if (!response.ok) throw new Error(`BMA ${response.status}`);
    const html = await response.text();
    const text = html.replace(/<script[\s\S]*?<\/script>/gi,' ').replace(/<style[\s\S]*?<\/style>/gi,' ').replace(/<[^>]+>/g,' ').replace(/&nbsp;/g,' ').replace(/\s+/g,' ').trim();
    const grab = (label) => {
      const m = text.match(new RegExp(label+'\\s*([0-9,]+)\\s*สถานี'));
      return m ? Number(m[1].replace(/,/g,'')) : null;
    };
    const flood = grab('น้ำท่วม(?!ขัง)');
    const minor = grab('น้ำท่วมขังเล็กน้อย');
    const normal = grab('ปกติ');
    const broken = grab('ขัดข้อง');
    const t = text.match(/วันที่\s*([^|]{3,80}?เวลา\s*[0-9]{1,2}:[0-9]{2})/);
    res.setHeader('Cache-Control','s-maxage=60, stale-while-revalidate=300');
    res.status(200).json({ok:true,counts:{flood,minor,normal,broken},sourceTime:t?t[1].trim():null,source:'https://floodbangkok.bangkok.go.th/',fetchedAt:new Date().toISOString()});
  } catch (error) {
    res.setHeader('Cache-Control','s-maxage=30, stale-while-revalidate=120');
    res.status(200).json({ok:false,error:'official_source_unavailable',source:'https://floodbangkok.bangkok.go.th/',fetchedAt:new Date().toISOString()});
  }
}
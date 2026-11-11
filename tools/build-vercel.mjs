import { cp, mkdir, writeFile } from 'node:fs/promises';
import { resolve } from 'node:path';
const backend = process.env.CHTS_BACKEND_URL;
if (!backend) throw new Error('Set CHTS_BACKEND_URL to the public HTTPS PHP application URL. Local XAMPP is not reachable from Vercel.');
const url = new URL(backend);
if (url.protocol !== 'https:' || url.username || url.password || url.search || url.hash || ['localhost','127.0.0.1','[::1]'].includes(url.hostname)) throw new Error('Use a public HTTPS backend URL without credentials, query or fragment.');
const origin = url.href.replace(/\/$/, '');
const output = resolve('.vercel/output');
const publicDir = resolve(output, 'static');
await mkdir(publicDir, { recursive: true });
for (const file of ['index.html','student-portal.html','sw.js','manifest.webmanifest','assets','images']) await cp(resolve(file), resolve(publicDir,file), { recursive:true });
await writeFile(resolve(output,'config.json'), JSON.stringify({version:3,routes:[
{src:'/api/(.*)',dest:origin+'/api/$1',headers:{'Cache-Control':'no-store'}},
{src:'/(?:sw\\.js|index\\.html|student-portal\\.html|manifest\\.webmanifest)',headers:{'Cache-Control':'no-cache'},continue:true},
{src:'/(?:images|assets)/(.*)',headers:{'Cache-Control':'public, max-age=86400'},continue:true},
{handle:'filesystem'}]},null,2));
console.log('Vercel build output ready.');

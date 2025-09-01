const API = {
  login: '/oauth/token',
  logout: '/oauth/logout',
  register: '/oauth/register',
  check: '/oauth/check',
  refresh: '/oauth/refresh',
  search: (q) => '/cars/search?q=' + encodeURIComponent(q || ''),
};

const state = {
  accessToken: null,
  refreshToken: null,
  interval: null
};

function $(sel, root=document){ return root.querySelector(sel); }
function el(tag, props={}, ...children){
  const n = Object.assign(document.createElement(tag), props);
  for(const c of children){ n.append(c); }
  return n;
}

function saveTokens(a, r){
  if(a) state.accessToken = a;
  if(r) state.refreshToken = r;
  if(a) localStorage.setItem('access_token', a);
  if(r) localStorage.setItem('refresh_token', r);
}

function loadTokens(){
  state.accessToken = localStorage.getItem('access_token');
  state.refreshToken = localStorage.getItem('refresh_token');
}

async function checkTokens() {
  const isTokenAlive = await authFetch()
  if (isTokenAlive) {
    if (!state.interval)
      state.interval = setInterval(checkTokens, 20000);
  } else {
    clearInterval(state.interval);
    state.interval = null;
    deleteTokens();
  }
}

function deleteTokens() {
  localStorage.removeItem('access_token');
  localStorage.removeItem('refresh_token');
  state.accessToken = null;
  state.refreshToken = null;
}

async function authFetch(opts={}){
  const headers = Object.assign({'Content-Type':'application/json'}, opts.headers||{});
  if(state.accessToken) headers['Authorization'] = 'Bearer ' + state.accessToken;
  try{
    const res = await fetch(API.check, {...opts, headers});
    if(res.status === 401 && state.refreshToken){

      const r = await fetch(API.refresh, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({refresh_token: state.refreshToken})});
      if(r.ok){
        const data = await r.json();
        saveTokens(data.access_token, data.refresh_token);
        return fetch(url, {...opts, headers:{...headers, Authorization:'Bearer ' + data.access_token}});
      } else {
        deleteTokens();
        return false
      }
    }
    return true;
  } catch(e) {
    console.log(e);
    return false;
  }
}

async function logout() {
  const headers = Object.assign({'Content-Type':'application/json'}, {});

  if(state.accessToken) {  
    deleteTokens();
    headers['Authorization'] = 'Bearer ' + state.accessToken;
    fetch(API.logout, {headers})
    location.reload(true);
  }
}

function card(item){
  const img = el('img', {src: item.photo_url || '/img/logo.svg', alt: item.name});
  const title = el('h3', {}, item.name);
  const badge = el('div', {className:'badge'}, [item.make, ' · ', item.model, ' · ', (item.year||'—')].join(' '));
  const desc = el('div', {className:'desc'}, item.description || '');
  const body = el('div', {className:'body'} , title, badge, desc);
  const card = el('div', {className:'card'}, img, body);
  return card;
}

async function load(q=''){
  const grid = $('#grid');
  grid.innerHTML = '';
  
  const headers = Object.assign({'Content-Type':'application/json'}, {});
  if(state.accessToken) headers['Authorization'] = 'Bearer ' + state.accessToken;
  const res = await fetch(API.search(q), {headers});

  if (res.status == 401) {
    return;
  }

  const items = await res.json();
  if(items.length === 0){
    grid.append(el('div', {className:'card'}, el('div', {className:'body'}, el('h3',{},'Ничего не найдено'), el('div',{className:'desc'},'Попробуйте другой запрос'))));
    return;
  }
  for(const item of items){
    grid.append(card(item));
  }
}

function toggleLoginButtons() {
  const openBtn = $('#openLogin');
  const logoutBtn = $('#openLogout');
  console.log(state.accessToken, state.refreshToken, state.accessToken && state.refreshToken)
  if (state.accessToken && state.refreshToken) {
    openBtn.classList.add('hidden');
    logoutBtn.classList.remove('hidden');
  } else {
    console.log(openBtn.classList, logoutBtn.classList);
    openBtn.classList.remove('hidden');
    logoutBtn.classList.add('hidden');    
    console.log(openBtn.classList, logoutBtn.classList);
  }
}

// Auth modal logic
function setupAuth(){
  const modal = $('#authModal');
  const openBtn = $('#openLogin');
  const logoutBtn = $('#openLogout')
  const closeBtn = $('#closeLogin');
  const tabs = document.querySelectorAll('.tab');
  const loginForm = $('#loginForm');
  const registerForm = $('#registerForm');

  openBtn.addEventListener('click', ()=>{ modal.classList.remove('hidden'); modal.setAttribute('aria-hidden','false'); });
  closeBtn.addEventListener('click', ()=>{ modal.classList.add('hidden'); modal.setAttribute('aria-hidden','true'); });
  logoutBtn.addEventListener('click', logout);

  toggleLoginButtons();

  tabs.forEach(tab=>{
    tab.addEventListener('click', ()=>{
      tabs.forEach(t=>t.classList.remove('active'));
      tab.classList.add('active');
      if(tab.dataset.tab === 'login'){ loginForm.classList.remove('hidden'); registerForm.classList.add('hidden'); }
      else { registerForm.classList.remove('hidden'); loginForm.classList.add('hidden'); }
    });
  });

  loginForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(loginForm);
    const res = await fetch(API.login, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({username: fd.get('username'), password: fd.get('password')})});
    const data = await res.json();
    if(!res.ok){ alert(data.error || 'Ошибка входа'); return; }
    saveTokens(data.access_token, data.refresh_token);
    toggleLoginButtons();
    modal.classList.add('hidden');
    await load($('#q').value);
  });

  registerForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(registerForm);
    const res = await fetch(API.register, {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({username: fd.get('username'), password: fd.get('password')})});
    const data = await res.json();
    if(!res.ok){ alert(data.error || 'Ошибка регистрации'); return; }
    alert('Аккаунт создан. Теперь войдите.');
  });
}

function setupSearch(){
  const form = $('#searchForm');
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    await load($('#q').value);
  });
}

(async function init(){
  loadTokens();
  await checkTokens();
  setupAuth();
  setupSearch();
  await load(''); // показать все
})();
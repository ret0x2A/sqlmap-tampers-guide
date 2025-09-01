
import requests
from lib.core.data import conf
from lib.core.data import logger
from lib.core.common import singleTimeWarnMessage
from lib.core.enums import PRIORITY

__priority__ = PRIORITY.NORMAL

def dependencies():
    singleTimeWarnMessage('Tamper demonstrates how to update session data. For more details, visit the repository: https://github.com/ret0x2A/sqlmap-tampers-guide/')

def get_base_url():
    '''
    Получение базового URL из конфига мапы
    '''
    base_url = f"{conf.scheme}://{conf.hostname}"
    if conf.port and conf.port not in (80, 443):
        base_url += f":{conf.port}"
    
    return base_url

def get_proxy():
    '''
    Получаем значение прокси из конфигурации sqlmap
    '''
    proxy = {}
   
    if conf.proxy:
        proxy_schema = conf.proxy.split(":")[0]
        proxy[proxy_schema] = conf.proxy
   
    return proxy

def get_tokens_from_headers():
    '''
    Ищем заголовки авторизации и обновления в конфигурации sqlmap
    '''
    headers_list = conf.headers or []
    access_token = None
    refresh_token = None
   
    for item in headers_list:
        if "Authorization" in item:
            access_token = item.split(' ')[-1]
        elif "X-Refresh-Token" in item:
            refresh_token = item.split(' ')[-1]
   
    return access_token, refresh_token

def set_or_replace_header(name, value):
    """
    Заменяет заголовок в conf.httpHeaders, 
    если он есть, иначе добавляет новый.
    """
    updated = False
    new_headers = []
    for header, val in conf.httpHeaders:
        if header.lower() == name.lower():
            new_headers.append((header, value))
            updated = True
        else:
            new_headers.append((header, val))
    if not updated:
        new_headers.append((name, value))
    conf.httpHeaders = new_headers

def check_token_alive(base_url, access_token, proxy):
    '''
    Проверяем, жив ли access_token, выполнив запрос на таргет
    '''

    headers = {
        "Authorization": f"Bearer {access_token}"
    }
    url = f"{base_url}/oauth/check"


    resp = requests.get(url, headers=headers, timeout=5, proxies=proxy)
    if resp.status_code == 401:
        return False
    
    print('Token alive')
    return True

def update_token(base_url, refresh_token, proxy):
    '''
    Получаем новые токены, чтобы sqlmap мог спокойно искать инъекцию
    '''
    refresh_resp = requests.post(
        f"{base_url}/oauth/refresh",
        json={"refresh_token": refresh_token},
        timeout=5,
        proxies=proxy
    )


    if refresh_resp.ok:
        data = refresh_resp.json()
        if "access_token" in data:
            access_token = data["access_token"]

        if "refresh_token" in data:
            refres_token = data["refresh_token"]
           
    return access_token, refresh_token


def tamper(payload, **kwargs):
    """
    Тампер для sqlmap: проверка и обновление Bearer access_token и refresh_token.
    Ожидает, что в заголовках есть:
      - Authorization: Bearer <ACCESS>
      - X-Refresh-Token: <REFRESH>
    """
   
    proxy = get_proxy()    
    access_token, refresh_token = get_tokens_from_headers()    

    if access_token or refresh_token:
        base_url = get_base_url()

        try:
            token_alive = check_token_alive(base_url=base_url, access_token=access_token,proxy=proxy)
            if not token_alive:
                logger.info('Need to update token')
                new_access_token, new_refresh_token = update_token(base_url=base_url, refresh_token=refresh_token, proxy=proxy)

                set_or_replace_header("Authorization", f"Bearer {new_access_token}")
                set_or_replace_header("X-Refresh-Token", new_refresh_token)

        except Exception as e:
            logger.critical(f"[tamper] Request failed: {e}")
            return payload

    return payload

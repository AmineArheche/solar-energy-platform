import os
import sys
from datetime import datetime

CURRENT_DIR = os.path.dirname(os.path.abspath(__file__))
if CURRENT_DIR not in sys.path:
    sys.path.insert(0, CURRENT_DIR)

from review_engine import execute_review_batch
import solar_day1_tasks
import solar_day2_tasks

def main():
    chosen = os.environ.get("CHOSEN_DAY", "auto").strip()
    today = datetime.now()
    
    # Détermination du jour (automatique selon date ou forcé)
    if chosen == "1":
        day = 1
    elif chosen == "2":
        day = 2
    else:
        # En mode auto : si on est le 7 octobre ou après, c'est le jour 2, sinon jour 1
        if today.month == 10 and today.day >= 7:
            day = 2
        else:
            day = 1

    print(f"=== DÉMARRAGE DU CODE REVIEW CLOUD : JOUR {day} ===")
    repo_root = os.path.abspath(os.path.join(CURRENT_DIR, "..", ".."))

    if day == 1:
        tasks = solar_day1_tasks.create_tasks()
        batch_name = "CloudReview_Solar_Day1_06Oct"
    else:
        tasks = solar_day2_tasks.create_tasks()
        batch_name = "CloudReview_Solar_Day2_07Oct"

    count = execute_review_batch(repo_root, tasks, batch_name)
    print(f"=== CODE REVIEW CLOUD TERMINÉ : {count}/{len(tasks)} CONTRIBUTIONS POUSSÉES DANS LE CLOUD ===")

if __name__ == "__main__":
    main()

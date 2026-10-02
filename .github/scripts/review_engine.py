"""
review_engine.py — Moteur d'exécution automatisé de Code Review & Commits Git
=============================================================================
Exécute une série de revues de code structurées (50 contributions par lot),
applique les modifications, effectue les commits conventionnels et pousse
vers GitHub avec journalisation complète.
"""

import os
import subprocess
import sys
import time
from datetime import datetime

def log_msg(msg, log_file=None):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    formatted = f"[{now}] {msg}"
    print(formatted, flush=True)
    if log_file:
        try:
            with open(log_file, "a", encoding="utf-8") as f:
                f.write(formatted + "\n")
        except Exception:
            pass

def run_cmd(cmd, cwd, log_file=None):
    try:
        res = subprocess.run(
            cmd,
            cwd=cwd,
            shell=True,
            capture_output=True,
            text=True,
            timeout=120
        )
        if res.stdout and res.stdout.strip():
            log_msg(f"  STDOUT: {res.stdout.strip()}", log_file)
        if res.stderr and res.stderr.strip():
            log_msg(f"  STDERR: {res.stderr.strip()}", log_file)
        return res.returncode == 0
    except Exception as e:
        log_msg(f"  ERREUR: {e}", log_file)
        return False

def execute_review_batch(repo_path, tasks, batch_name, log_filename=None):
    if not os.path.exists(repo_path):
        print(f"Erreur: Répertoire {repo_path} introuvable.")
        return False

    logs_dir = os.path.join(os.path.dirname(__file__), "logs")
    os.makedirs(logs_dir, exist_ok=True)
    if not log_filename:
        log_filename = f"{batch_name}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.log"
    log_path = os.path.join(logs_dir, log_filename)

    log_msg(f"==================================================", log_path)
    log_msg(f"DÉMARRAGE CODE REVIEW : {batch_name}", log_path)
    log_msg(f"Dépôt : {repo_path}", log_path)
    log_msg(f"Total de contributions planifiées : {len(tasks)}", log_path)
    log_msg(f"==================================================", log_path)

    # Vérification et synchronisation préliminaire
    log_msg("Vérification git pull origin main...", log_path)
    run_cmd("git pull origin main --rebase", cwd=repo_path, log_file=log_path)

    success_count = 0
    total = len(tasks)

    for idx, task in enumerate(tasks, start=1):
        log_msg(f"\n[{idx}/{total}] Exécution : {task['title']}", log_path)
        commit_msg = task["commit_msg"]
        apply_fn = task["apply"]
        target_files = task.get("files", ["."])

        try:
            # 1. Appliquer les changements du code review
            applied = apply_fn(repo_path)
            if not applied:
                log_msg(f"  [SKIP] Aucun changement ou modification échouée.", log_path)
                continue

            # 2. git add
            files_str = " ".join([f'"{f}"' for f in target_files])
            add_ok = run_cmd(f"git add {files_str}", cwd=repo_path, log_file=log_path)
            if not add_ok:
                log_msg(f"  [ERREUR] git add a échoué.", log_path)
                continue

            # Vérifier si des changements sont staggés
            check_staged = subprocess.run(
                "git diff --cached --quiet",
                cwd=repo_path,
                shell=True
            )
            if check_staged.returncode == 0:
                log_msg(f"  [INFO] Aucun diff détecté (déjà à jour).", log_path)
                continue

            # 3. git commit
            safe_msg = commit_msg.replace('"', '\\"')
            commit_ok = run_cmd(f'git commit -m "{safe_msg}"', cwd=repo_path, log_file=log_path)
            if not commit_ok:
                log_msg(f"  [ERREUR] git commit a échoué.", log_path)
                continue

            # 4. git push
            push_ok = run_cmd("git push origin main", cwd=repo_path, log_file=log_path)
            if not push_ok:
                log_msg(f"  [RETRY] Tentative de rebase et second push...", log_path)
                run_cmd("git pull origin main --rebase", cwd=repo_path, log_file=log_path)
                push_ok = run_cmd("git push origin main", cwd=repo_path, log_file=log_path)

            if push_ok:
                success_count += 1
                log_msg(f"  [SUCCÈS] Contribution {idx}/{total} enregistrée et poussée !", log_path)
            else:
                log_msg(f"  [AVERTISSEMENT] Commit local OK mais le push distant a échoué.", log_path)

            time.sleep(1.5)

        except Exception as err:
            log_msg(f"  [EXCEPTION] Erreur inattendue sur la tâche {idx} : {err}", log_path)

    log_msg(f"\n==================================================", log_path)
    log_msg(f"CODE REVIEW TERMINÉ : {success_count}/{total} contributions poussées avec succès.", log_path)
    log_msg(f"Journal complet disponible dans : {log_path}", log_path)
    log_msg(f"==================================================", log_path)
    return success_count

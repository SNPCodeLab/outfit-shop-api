# Rule: Never Auto-Merge

NEVER merge any branch into docs or main unless the user explicitly commands it
using one of these exact words or phrases:

  pm   mp   push merge   merge push   push and merge   merge and push

Any other phrasing, even "deploy", "push it", "ship it", or "save it", does NOT
count as a merge command. Only the exact trigger words above authorize a merge.

## What pm / mp means

When the user says pm or mp:
1. Stage and commit any pending changes on the current branch.
2. Push the current branch to origin.
3. Merge the current branch into docs (via fast-forward or no-ff commit).
4. Push docs to origin.
5. Fast-forward main to match docs.
6. Push main to origin.
7. Delete the working branch locally and on remote (if it is not docs or main).

## Branch Vocabulary

| Term | Meaning |
|------|---------|
| local | Your machine. Changes exist only here until pushed. |
| remote / origin | GitHub (github.com/SNPCodeLab/outfit-shop-api). What others see. |
| origin/docs | The docs branch as it exists on GitHub right now. |
| docs (local) | Your local copy of docs. May be behind origin/docs until you pull. |
| HEAD | The commit you are currently on locally. |

Pushing = sending local commits to origin.
Merging = combining branches.
pm = push then merge to docs then sync main — all in one command.

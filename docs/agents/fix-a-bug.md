# Fix a bug

A bug is a missing test. The fix proves the test green.

## The loop

1. **Reproduce in a test, not in a browser.** Write the smallest Pest test that
   fails today and passes when the bug is gone. Name it after the behavior:
   `it('does not show draft releases to guests')`.
2. **Prove the test is real.** Run it, watch it fail for the right reason
   (assertion, not error).
3. **Fix the cause, not the symptom.** If two tests fail for one bug, fix the
   code once — never patch around each test.
4. **Re-run the whole suite**, not just your test: `composer test`. A fix that
   breaks a neighbor is not a fix.
5. **One PR per bug**: branch `fix/<topic>`, the failing-test commit first, the
   fix commit second. The reviewer sees the red proof, then the green cure.

## Rules that bite here

- Never delete a failing test. Diagnose in `tmp/test-fix.md` (gitignored).
- If the bug is a product decision instead ("this should work differently"),
  stop and ask the human — do not pick a side alone.
- If the fix touches a constraint ("user_id must stay non-unique until
  duplicates are migrated"), that constraint gets a comment. That is the one
  kind of comment that earns its line.

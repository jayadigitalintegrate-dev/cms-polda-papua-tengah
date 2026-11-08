import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import assert from 'node:assert/strict';
import test from 'node:test';

const blade = readFileSync(new URL('../../resources/views/admin/complaints/index.blade.php', import.meta.url), 'utf8');
const script = blade.match(/<script>([\s\S]*?)<\/script>/)[1];

function setup(size = 15) {
    const element = () => ({
        checked: false, disabled: false, value: '', handlers: {},
        addEventListener(name, handler) { this.handlers[name] = handler; },
    });
    const ids = Object.fromEntries(['complaint-bulk-delete', 'complaint-select-all', 'complaint-selection-count',
        'complaint-delete-selected'].map(id => [id, element()]));
    const rows = Array.from({ length: size }, element);
    const offPageRow = element();
    const confirmations = [];
    const window = { addEventListener() {}, confirm(message) { confirmations.push(message); return true; } };
    runInNewContext(script, {
        document: {
            getElementById: id => ids[id],
            querySelectorAll(selector) { assert.equal(selector, '[data-complaint-selection]'); return rows; },
        }, window,
    });
    return { ids, rows, offPageRow, confirmations, window };
}

test('select all affects visible rows, count and indeterminate state only', () => {
    const { ids, rows, offPageRow } = setup();
    const all = ids['complaint-select-all'];
    assert.equal(ids['complaint-delete-selected'].disabled, true);
    all.checked = true;
    all.handlers.change();
    assert.ok(rows.every(row => row.checked));
    assert.equal(offPageRow.checked, false);
    assert.equal(ids['complaint-selection-count'].textContent, '15 pengaduan dipilih');
    assert.equal(ids['complaint-delete-selected'].disabled, false);
    rows[0].checked = false;
    rows[0].handlers.change();
    assert.equal(all.indeterminate, true);
    assert.equal(all.checked, false);
    all.checked = false;
    all.handlers.change();
    assert.equal(ids['complaint-delete-selected'].disabled, true);
});

test('empty selection cannot submit; selection alone confirms count and can cancel', () => {
    const { ids, rows, confirmations, window } = setup(2);
    let prevented;
    const submit = () => {
        prevented = false;
        ids['complaint-bulk-delete'].handlers.submit({ preventDefault() { prevented = true; } });
    };
    submit();
    assert.equal(prevented, true);
    assert.equal(confirmations.length, 0);
    rows.forEach(row => { row.checked = true; });
    submit();
    assert.equal(prevented, false);
    assert.match(confirmations[0], /Hapus 2 pengaduan terpilih/);
    window.confirm = () => false;
    submit();
    assert.equal(prevented, true);
});

test('empty page disables select all and deletion', () => {
    const { ids } = setup(0);
    assert.equal(ids['complaint-select-all'].disabled, true);
    assert.equal(ids['complaint-delete-selected'].disabled, true);
});

test('more than 15 selections cannot submit', () => {
    const { ids, confirmations } = setup(16);
    ids['complaint-select-all'].checked = true;
    ids['complaint-select-all'].handlers.change();
    assert.equal(ids['complaint-delete-selected'].disabled, true);
    let prevented = false;
    ids['complaint-bulk-delete'].handlers.submit({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(confirmations.length, 0);
});

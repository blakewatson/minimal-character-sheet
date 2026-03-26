/**
 * Character sheet export — downloads JSON + Markdown files for a given sheet.
 */

/**
 * Sanitize a character name for use as a filename.
 * @param {string} name
 * @returns {string}
 */
export function sanitizeFilename(name) {
  if (!name || !name.trim()) return 'character';
  var result = name.trim();
  result = result.replace(/[<>:"/\\|?*\x00-\x1f]/g, '');
  result = result.replace(/\s+/g, '-');
  result = result.slice(0, 100);
  if (!result) return 'character';
  return result;
}

/**
 * Trigger a file download in the browser via Blob URL.
 * @param {string} content
 * @param {string} filename
 * @param {string} mimeType
 */
function downloadFile(content, filename, mimeType) {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

/**
 * Extract plain text from a Quill delta object.
 * @param {object|string} delta
 * @returns {string}
 */
function extractQuillText(delta) {
  if (!delta) return '';
  if (typeof delta === 'string') return delta;
  if (!delta.ops || !Array.isArray(delta.ops)) return '';
  var text = '';
  delta.ops.forEach(function (op) {
    if (typeof op.insert === 'string') {
      text += op.insert;
    }
  });
  return text.replace(/\n+$/, '');
}

/**
 * Generate a full Markdown representation of a character sheet.
 * @param {object} data - The sheet data object (same shape as store state)
 * @param {boolean} is_2024 - Whether the sheet uses 2024 rules
 * @returns {string}
 */
function generateMarkdown(data, is_2024) {
  var lines = [];

  lines.push('# ' + (data.characterName || 'Untitled Character'));
  lines.push('');
  lines.push(
    '**Race:** ' +
      (data.race || '') +
      ' | **Class:** ' +
      (data.className || '') +
      ' | **Level:** ' +
      (data.level || ''),
  );
  lines.push(
    '**Background:** ' +
      (data.background || '') +
      ' | **Alignment:** ' +
      (data.alignment || '') +
      ' | **XP:** ' +
      (data.xp || 0),
  );
  lines.push('**Rules:** ' + (is_2024 ? '2024' : '2014'));
  lines.push('');

  // Vitals
  lines.push('## Vitals');
  lines.push('');
  lines.push(
    '- **HP:** ' +
      (data.hp || 0) +
      '/' +
      (data.maxHp || 0) +
      ' (Temp: ' +
      (data.tempHp || 0) +
      ')',
  );
  lines.push(
    '- **AC:** ' +
      (data.ac || 0) +
      ' | **Speed:** ' +
      (data.speed || 0) +
      ' | **Initiative:** ' +
      (data.initiative || 0),
  );
  lines.push(
    '- **Hit Die:** ' +
      (data.hitDie || '') +
      ' (Total: ' +
      (data.totalHitDie || 0) +
      ')',
  );
  lines.push('- **Inspiration:** ' + (data.inspiration ? 'Yes' : 'No'));
  lines.push('- **Conditions:** ' + (data.conditions || 'None'));
  lines.push('- **Concentration:** ' + (data.concentration || 'None'));
  lines.push('');

  // Ability Scores
  if (data.abilities && data.abilities.length > 0) {
    lines.push('## Ability Scores');
    lines.push('');
    lines.push(
      '| ' +
        data.abilities
          .map(function (a) {
            return a.name;
          })
          .join(' | ') +
        ' |',
    );
    lines.push(
      '|' +
        data.abilities
          .map(function () {
            return '-----';
          })
          .join('|') +
        '|',
    );
    lines.push(
      '| ' +
        data.abilities
          .map(function (a) {
            return a.score;
          })
          .join(' | ') +
        ' |',
    );
    lines.push('');
  }

  // Saving Throws
  if (data.savingThrows && data.savingThrows.length > 0) {
    lines.push('## Saving Throws');
    lines.push('');
    data.savingThrows.forEach(function (st) {
      lines.push('- [' + (st.proficient ? 'x' : ' ') + '] ' + st.name);
    });
    lines.push('');
  }

  // Skills
  if (data.skills && data.skills.length > 0) {
    lines.push('## Skills');
    lines.push('');
    data.skills.forEach(function (skill) {
      var label = skill.name + ' (' + skill.ability + ')';
      if (skill.doubleProficient) {
        label += ' (expertise)';
      }
      lines.push(
        '- [' +
          (skill.proficient || skill.doubleProficient ? 'x' : ' ') +
          '] ' +
          label,
      );
    });
    lines.push('');
  }

  // Attacks
  if (data.attacks && data.attacks.length > 0) {
    lines.push('## Attacks');
    lines.push('');
    lines.push('| Name | Attack Bonus | Damage | Notes |');
    lines.push('|------|-------------|--------|-------|');
    data.attacks.forEach(function (atk) {
      lines.push(
        '| ' +
          (atk.name || '') +
          ' | ' +
          (atk.attackBonus || '') +
          ' | ' +
          (atk.damage || '') +
          ' | ' +
          (atk.weaponNotes || '') +
          ' |',
      );
    });
    lines.push('');
  }

  // Trackable Fields
  if (data.trackableFields && data.trackableFields.length > 0) {
    lines.push('## Trackable Fields');
    lines.push('');
    lines.push('| Name | Used | Max | Notes |');
    lines.push('|------|------|-----|-------|');
    data.trackableFields.forEach(function (tf) {
      lines.push(
        '| ' +
          (tf.name || '') +
          ' | ' +
          (tf.used || 0) +
          ' | ' +
          (tf.max || 0) +
          ' | ' +
          (tf.notes || '') +
          ' |',
      );
    });
    lines.push('');
  }

  // Rich text sections
  var richTextSections = [
    { key: 'equipmentText', title: 'Equipment' },
    { key: 'proficienciesText', title: 'Proficiencies & Languages' },
    { key: 'featuresText', title: 'Features & Traits' },
    { key: 'personalityText', title: 'Personality' },
    { key: 'backstoryText', title: 'Backstory' },
    { key: 'treasureText', title: 'Treasure' },
    { key: 'organizationsText', title: 'Organizations' },
    { key: 'notesText', title: 'Notes' },
  ];

  richTextSections.forEach(function (section) {
    var text = extractQuillText(data[section.key]);
    if (text) {
      lines.push('## ' + section.title);
      lines.push('');
      lines.push(text);
      lines.push('');
    }
  });

  // Coins
  if (data.coins && data.coins.length > 0) {
    lines.push('## Coins');
    lines.push('');
    lines.push(
      '| ' +
        data.coins
          .map(function (c) {
            return c.name.toUpperCase();
          })
          .join(' | ') +
        ' |',
    );
    lines.push(
      '|' +
        data.coins
          .map(function () {
            return '----';
          })
          .join('|') +
        '|',
    );
    lines.push(
      '| ' +
        data.coins
          .map(function (c) {
            return c.amount;
          })
          .join(' | ') +
        ' |',
    );
    lines.push('');
  }

  // Spells
  var hasSpells =
    data.spClass ||
    (data.cantripsList && data.cantripsList.length > 0) ||
    hasAnySpellLevel(data);

  if (hasSpells) {
    lines.push('## Spells');
    lines.push('');
    lines.push(
      '**Class:** ' +
        (data.spClass || '') +
        ' | **Ability:** ' +
        (data.spAbility || '') +
        ' | **Save DC:** ' +
        (data.spSave || '') +
        ' | **Attack Bonus:** ' +
        (data.spAttack || ''),
    );
    lines.push('');

    // Cantrips
    if (data.cantripsList && data.cantripsList.length > 0) {
      lines.push('### Cantrips');
      lines.push('');
      data.cantripsList.forEach(function (cantrip) {
        if (cantrip.val) {
          lines.push('- ' + cantrip.val);
        }
      });
      lines.push('');
    }

    // Spell levels 1-9
    for (var i = 1; i <= 9; i++) {
      var levelKey = 'lvl' + i + 'Spells';
      var levelData = data[levelKey];
      if (
        levelData &&
        (levelData.slots > 0 ||
          (levelData.spells && levelData.spells.length > 0))
      ) {
        lines.push(
          '### Level ' +
            i +
            ' (Slots: ' +
            (levelData.slots || 0) +
            ', Expended: ' +
            (levelData.expended || 0) +
            ')',
        );
        lines.push('');
        if (levelData.spells && levelData.spells.length > 0) {
          levelData.spells.forEach(function (spell) {
            if (spell.name) {
              lines.push(
                '- [' + (spell.prepared ? 'x' : ' ') + '] ' + spell.name,
              );
            }
          });
        } else {
          lines.push('None');
        }
        lines.push('');
      }
    }
  }

  return lines.join('\n');
}

/**
 * Check if any spell level 1-9 has slots or spells.
 * @param {object} data
 * @returns {boolean}
 */
function hasAnySpellLevel(data) {
  for (var i = 1; i <= 9; i++) {
    var levelData = data['lvl' + i + 'Spells'];
    if (
      levelData &&
      (levelData.slots > 0 || (levelData.spells && levelData.spells.length > 0))
    ) {
      return true;
    }
  }
  return false;
}

/**
 * Export a character sheet as JSON + Markdown files.
 * Fetches full sheet data from the server, then triggers two downloads.
 * @param {string} slug - The sheet slug
 * @param {string} name - The character name (for filenames)
 */
export async function exportSheet(slug, name) {
  try {
    var resp = await fetch('/sheet-data/' + slug);
    var json = await resp.json();

    if (!json.success || !json.sheet) {
      console.error('Failed to fetch sheet data for export', json);
      return;
    }

    var sheetData = json.sheet.data;
    var is_2024 = json.sheet.is_2024;
    var fileBase = sanitizeFilename(name);

    downloadFile(
      JSON.stringify(sheetData, null, 2),
      fileBase + '.json',
      'application/json',
    );

    setTimeout(function () {
      downloadFile(
        generateMarkdown(sheetData, is_2024),
        fileBase + '.md',
        'text/markdown',
      );
    }, 100);
  } catch (err) {
    console.error('Export failed', err);
  }
}

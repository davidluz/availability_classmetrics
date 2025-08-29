YUI.add('moodle-availability_classmetrics-form', function(Y, NAME) {

    M.availability_classmetrics = M.availability_classmetrics || {};
    M.availability_classmetrics.form = Y.Object(M.core_availability.plugin);
    M.availability_classmetrics.form.type = 'classmetrics';

    // Recebe dados do PHP.
    M.availability_classmetrics.form.initInner = function(cms, groups) {
        this.cms = cms || [];
        this.groups = groups || [];
        // (Não dependemos mais de delegate global aqui.)
    };

    // Helpers ---------------------------------------------------------------
    function getCurrentRule(node) {
        var ruleName = node.getAttribute('data-rule-name');
        var rb = ruleName ? node.one('input[name="' + ruleName + '"]:checked') : null;
        return (rb && rb.get('value') === 'minstudents') ? 'minstudents' : 'percent';
    }
    function syncBlocksVisibility(node, rule) {
        var percentWrap = node.one('.percentblock');
        var minWrap = node.one('.minblock');
        if (percentWrap) { percentWrap.setStyle('display', rule === 'percent' ? '' : 'none'); }
        if (minWrap) { minWrap.setStyle('display', rule === 'minstudents' ? '' : 'none'); }
    }

    // Monta a UI.
    M.availability_classmetrics.form.getNode = function(json) {
        json = json || {};
        var strings = M.str['availability_classmetrics'] || {};

        var rule = json.rule || (this.cms.length ? 'percent' : 'minstudents');
        var groupid = json.groupid || 0;
        var aggregation = (json.aggregation === 'any') ? 'any' : 'all';
        var percent = (typeof json.percent !== 'undefined') ? json.percent : 0;
        var minstudents = (typeof json.minstudents !== 'undefined') ? json.minstudents : 0;
        var activities = json.activities || [];

        var root = Y.Node.create('<div class="availability_classmetrics"></div>');

        // Nomes ÚNICOS por instância para evitar colisões.
        var ruleName = 'acm_rule_' + Y.guid();
        var aggName  = 'acm_agg_' + Y.guid();
        root.setAttribute('data-rule-name', ruleName);
        root.setAttribute('data-agg-name', aggName);

        // --- Regra
        var ruleWrap = Y.Node.create('<div class="form-group"></div>');
        ruleWrap.append('<label>' + (strings.rule || 'Regra') + '</label><br>');
        var rbPercent = Y.Node.create('<label><input type="radio" name="' + ruleName + '" value="percent"> ' + (strings.rule_percent || '% de conclusão (turma)') + '</label> ');
        var rbMin     = Y.Node.create('<label><input type="radio" name="' + ruleName + '" value="minstudents"> ' + (strings.rule_minstudents || 'Nº mínimo de alunos (turma)') + '</label>');
        rbPercent.one('input').set('checked', rule === 'percent');
        rbMin.one('input').set('checked', rule === 'minstudents');
        ruleWrap.append(rbPercent);
        ruleWrap.append(rbMin);
        root.append(ruleWrap);

        // --- Grupo
        var groupWrap = Y.Node.create('<div class="form-group groupblock"></div>');
        groupWrap.append('<label>' + (strings.group || 'Filtrar por grupo (opcional)') + '</label><br>');
        var gsel = Y.Node.create('<select class="custom-select" name="groupid"></select>');
        Y.Array.each(this.groups, function(g) {
            var opt = Y.Node.create('<option></option>');
            opt.setAttribute('value', g.id);
            opt.setHTML(Y.Escape.html(g.name));
            if (groupid == g.id) { opt.setAttribute('selected', 'selected'); }
            gsel.append(opt);
        });
        groupWrap.append(gsel);
        root.append(groupWrap);

        // --- Bloco "percent"
        var percentWrap = Y.Node.create('<div class="form-group percentblock"></div>');
        percentWrap.append('<label>' + (strings.activities || 'Atividades-alvo') + '</label><br>');
        var actsSelect = Y.Node.create('<select multiple size="6" class="custom-select" name="activities"></select>');
        Y.Array.each(this.cms, function(cm) {
            var opt = Y.Node.create('<option></option>');
            opt.setAttribute('value', cm.id);
            opt.setHTML(Y.Escape.html(cm.name));
            if (Y.Array.indexOf(activities, cm.id) !== -1) { opt.setAttribute('selected', 'selected'); }
            actsSelect.append(opt);
        });
        percentWrap.append(actsSelect);

        var aggWrap = Y.Node.create('<div class="mt-2"></div>');
        aggWrap.append('<label>' + (strings.aggregation || 'Agregação entre atividades') + '</label><br>');
        var rbAll = Y.Node.create('<label><input type="radio" name="' + aggName + '" value="all"> ' + (strings.aggregation_all || 'TODAS') + '</label> ');
        var rbAny = Y.Node.create('<label><input type="radio" name="' + aggName + '" value="any"> ' + (strings.aggregation_any || 'QUALQUER') + '</label>');
        rbAll.one('input').set('checked', aggregation === 'all');
        rbAny.one('input').set('checked', aggregation === 'any');
        aggWrap.append(rbAll);
        aggWrap.append(rbAny);
        percentWrap.append(aggWrap);

        var pWrap = Y.Node.create('<div class="mt-2"></div>');
        pWrap.append('<label>' + (strings.percent || 'Percentual mínimo (%)') + '</label><br>');
        var pin = Y.Node.create('<input type="number" class="form-control" min="0" max="100" name="percent">');
        pin.set('value', percent);
        pWrap.append(pin);
        percentWrap.append(pWrap);
        root.append(percentWrap);

        // --- Bloco "minstudents"
        var minWrap = Y.Node.create('<div class="form-group minblock"></div>');
        minWrap.append('<label>' + (strings.minstudents || 'Número mínimo de alunos') + '</label><br>');
        var minin = Y.Node.create('<input type="number" class="form-control" min="0" name="minstudents">');
        minin.set('value', minstudents);
        minWrap.append(minin);
        root.append(minWrap);

        // Visibilidade inicial baseada no rádio.
        syncBlocksVisibility(root, getCurrentRule(root));

        // Alternância (UI).
        rbPercent.one('input').on('change', function() { syncBlocksVisibility(root, 'percent'); });
        rbMin.one('input').on('change', function() { syncBlocksVisibility(root, 'minstudents'); });

        // === Delegação local de eventos (independente do olho/tema/etc) ===
        // Qualquer mudança dentro do nosso container dispara update do core.
        root.delegate('change', function() {
            if (M && M.core_availability && M.core_availability.form && typeof M.core_availability.form.update === 'function') {
                M.core_availability.form.update();
            }
        }, 'select, input');

        // Dispara um update inicial (garante que o JSON reflita o estado visível agora).
        Y.later(0, null, function() {
            if (M && M.core_availability && M.core_availability.form && typeof M.core_availability.form.update === 'function') {
                M.core_availability.form.update();
            }
        });

        return root;
    };

    // Serializa para JSON.
    M.availability_classmetrics.form.fillValue = function(value, node) {
        value.type = 'classmetrics';

        var rule = getCurrentRule(node);
        value.rule = rule;

        var gsel = node.one('.groupblock select[name=groupid]');
        value.groupid = parseInt(gsel ? gsel.get('value') : 0, 10) || 0;

        if (rule === 'percent') {
            var acts = [];
            node.all('.percentblock select[name=activities] option').each(function(opt){
                if (opt.getDOMNode().selected) {
                    acts.push(parseInt(opt.get('value'), 10));
                }
            });
            value.activities = acts;

            var aggName = node.getAttribute('data-agg-name');
            var aggInput = aggName ? node.one('input[name="' + aggName + '"]:checked') : null;
            value.aggregation = (aggInput && aggInput.get('value') === 'any') ? 'any' : 'all';

            var pNode = node.one('.percentblock input[name=percent]');
            var p = parseInt(pNode ? pNode.get('value') : 0, 10);
            value.percent = isNaN(p) ? 0 : Math.max(0, Math.min(100, p));

            delete value.minstudents;
        } else {
            var mNode = node.one('.minblock input[name=minstudents]');
            var m = parseInt(mNode ? mNode.get('value') : 0, 10);
            value.minstudents = isNaN(m) ? 0 : Math.max(0, m);

            delete value.activities;
            delete value.aggregation;
            delete value.percent;
        }
    };

    // Validação baseada em fillValue (padrão core).
    M.availability_classmetrics.form.fillErrors = function(errors, node) {
        var v = {};
        this.fillValue(v, node);

        if (v.rule === 'percent') {
            if (!v.activities || !v.activities.length) {
                errors.push('availability_classmetrics:error_noactivities');
            }
            var p = parseInt(v.percent, 10);
            if (isNaN(p) || p < 0 || p > 100) {
                errors.push('availability_classmetrics:error_percent');
            }
        } else {
            var m = parseInt(v.minstudents, 10);
            if (isNaN(m) || m < 0) {
                errors.push('availability_classmetrics:error_minstudents');
            }
        }

        for (var i = errors.length - 1; i >= 0; i--) {
            if (!errors[i] || typeof errors[i] !== 'string') {
                errors.splice(i, 1);
            }
        }
    };

    // Registro do plugin quando o core estiver pronto (hotfix).
    (function registerWhenReady(tries){
        try {
            if (M && M.core_availability && M.core_availability.form) {
                if (typeof M.core_availability.form.addPlugin === 'function') {
                    M.core_availability.form.addPlugin(M.availability_classmetrics.form);
                    return;
                }
                if (typeof M.core_availability.form.add_plugin === 'function') {
                    M.core_availability.form.add_plugin(M.availability_classmetrics.form);
                    return;
                }
            }
        } catch(e) {}
        if (tries > 0) {
            Y.later(50, null, registerWhenReady, [tries - 1]);
        } else {
            Y.log('availability_classmetrics: core_availability.form.addPlugin indisponível', 'error', 'availability_classmetrics');
        }
    })(50);

}, '1.0.7', { requires: ['base', 'node', 'event', 'moodle-core_availability-form'] });

YUI.add('moodle-availability_classmetrics-form', function(Y, NAME) {

    M.availability_classmetrics = M.availability_classmetrics || {};
    // Herda a API base do editor de disponibilidade.
    M.availability_classmetrics.form = Y.Object(M.core_availability.plugin);
    // Deixa explícito o tipo do plugin (ajuda o core a montar o JSON).
    M.availability_classmetrics.form.type = 'classmetrics';

    // Recebe dados do PHP (lista de atividades e grupos).
    M.availability_classmetrics.form.initInner = function(cms, groups) {
        this.cms = cms || [];
        this.groups = groups || [];

        // Delegação de eventos (uma única vez), igual aos plugins core.
        if (!M.availability_classmetrics.form.addedEvents) {
            M.availability_classmetrics.form.addedEvents = true;
            var root = Y.one('.availability-field');
            if (root) {
                root.delegate('change', function() {
                    M.core_availability.form.update();
                }, '.availability_classmetrics select, .availability_classmetrics input');
            }
        }
    };

    // Helpers ------------------------------

    function getCurrentRule(node) {
        // Regra SEMPRE lida do rádio dentro do node (determinístico).
        var rb = node.one('input[name=rule]:checked');
        return (rb && rb.get('value') === 'minstudents') ? 'minstudents' : 'percent';
    }

    function syncBlocksVisibility(node, rule) {
        var percentWrap = node.one('.percentblock');
        var minWrap = node.one('.minblock');
        if (percentWrap) { percentWrap.setStyle('display', rule === 'percent' ? '' : 'none'); }
        if (minWrap) { minWrap.setStyle('display', rule === 'minstudents' ? '' : 'none'); }
    }

    // Constrói os campos do editor.
    M.availability_classmetrics.form.getNode = function(json) {
        json = json || {};
        var strings = M.str['availability_classmetrics'] || {};

        var rule = json.rule || (this.cms.length ? 'percent' : 'minstudents');
        var groupid = json.groupid || 0;
        var aggregation = (json.aggregation === 'any') ? 'any' : 'all';
        var percent = (typeof json.percent !== 'undefined') ? json.percent : 0;
        var minstudents = (typeof json.minstudents !== 'undefined') ? json.minstudents : 0;
        var activities = json.activities || [];

        // Root do plugin (o core o envolve com um wrapper .availability_classmetrics).
        var root = Y.Node.create('<div class="availability_classmetrics"></div>');

        // --- Regra
        var ruleWrap = Y.Node.create('<div class="form-group"></div>');
        ruleWrap.append('<label>' + (strings.rule || 'Regra') + '</label><br>');
        var rbPercent = Y.Node.create('<label><input type="radio" name="rule" value="percent"> ' + (strings.rule_percent || '% de conclusão (turma)') + '</label> ');
        var rbMin     = Y.Node.create('<label><input type="radio" name="rule" value="minstudents"> ' + (strings.rule_minstudents || 'Nº mínimo de alunos (turma)') + '</label>');
        rbPercent.one('input').set('checked', rule === 'percent');
        rbMin.one('input').set('checked', rule === 'minstudents');
        ruleWrap.append(rbPercent);
        ruleWrap.append(rbMin);
        root.append(ruleWrap);

        // --- Grupo
        var groupWrap = Y.Node.create('<div class="form-group groupblock"></div>');
        groupWrap.append('<label>' + (strings.group || 'Grupo') + '</label><br>');
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
        var rbAll = Y.Node.create('<label><input type="radio" name="aggr" value="all"> ' + (strings.aggregation_all || 'TODAS') + '</label> ');
        var rbAny = Y.Node.create('<label><input type="radio" name="aggr" value="any"> ' + (strings.aggregation_any || 'QUALQUER') + '</label>');
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

        // Sincroniza visibilidade de blocos conforme valor atual do rádio.
        syncBlocksVisibility(root, getCurrentRule(root));

        // Alternância (apenas UI; leitura real da regra é sempre pelo rádio).
        rbPercent.one('input').on('change', function() { syncBlocksVisibility(root, 'percent'); });
        rbMin.one('input').on('change', function() { syncBlocksVisibility(root, 'minstudents'); });

        return root;
    };

    // Serializa os valores para o JSON que o Moodle salva.
    M.availability_classmetrics.form.fillValue = function(value, node) {
        // Define explicitamente o tipo do plugin (garante JSON correto no POST).
        value.type = 'classmetrics';

        var rule = getCurrentRule(node);
        value.rule = rule;

        var gsel = node.one('.groupblock select[name=groupid]');
        value.groupid = parseInt(gsel ? gsel.get('value') : 0, 10) || 0;

        if (rule === 'percent') {
            // Atividades selecionadas — PROPRIEDADE DOM 'selected'
            var acts = [];
            node.all('.percentblock select[name=activities] option').each(function(opt){
                if (opt.getDOMNode().selected) {
                    acts.push(parseInt(opt.get('value'), 10));
                }
            });
            value.activities = acts;

            // Agregação.
            var aggInput = node.one('input[name=aggr]:checked');
            value.aggregation = (aggInput && aggInput.get('value') === 'any') ? 'any' : 'all';

            // Percentual.
            var pNode = node.one('.percentblock input[name=percent]');
            var p = parseInt(pNode ? pNode.get('value') : 0, 10);
            value.percent = isNaN(p) ? 0 : Math.max(0, Math.min(100, p));

            // Limpa chaves da outra regra.
            delete value.minstudents;
        } else {
            var mNode = node.one('.minblock input[name=minstudents]');
            var m = parseInt(mNode ? mNode.get('value') : 0, 10);
            value.minstudents = isNaN(m) ? 0 : Math.max(0, m);

            // Limpa chaves da outra regra.
            delete value.activities;
            delete value.aggregation;
            delete value.percent;
        }
    };

    // Validação client-side: valida em cima do objeto montado por fillValue (padrão core).
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

        // Sanitiza.
        for (var i = errors.length - 1; i >= 0; i--) {
            if (!errors[i] || typeof errors[i] !== 'string') {
                errors.splice(i, 1);
            }
        }
    };

    // Registro do plugin somente quando o core_availability estiver pronto (hotfix).
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
        } catch(e) { /* tenta de novo */ }
        if (tries > 0) {
            Y.later(50, null, registerWhenReady, [tries - 1]);
        } else {
            Y.log('availability_classmetrics: core_availability.form.addPlugin indisponível', 'error', 'availability_classmetrics');
        }
    })(50);

}, '1.0.5', { requires: ['base', 'node', 'event', 'moodle-core_availability-form'] });

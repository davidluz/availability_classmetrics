YUI.add('moodle-availability_classmetrics-form', function(Y, NAME) {

     M.availability_classmetrics = M.availability_classmetrics || {};
    // Herda a API do plugin base do editor de disponibilidade.
    M.availability_classmetrics.form = Y.Object(M.core_availability.plugin);
    // Recebe dados do PHP (lista de atividades e grupos).
    M.availability_classmetrics.form.initInner = function(cms, groups) {
        this.cms = cms || [];
        this.groups = groups || [];
    };
    // Constrói os campos do editor.
    M.availability_classmetrics.form.getNode = function(json) {
        var strings = M.str['availability_classmetrics'] || {};
        json = json || {};
        var rule = json.rule || (this.cms.length ? 'percent' : 'minstudents');
        var groupid = json.groupid || 0;
        var aggregation = (json.aggregation === 'any') ? 'any' : 'all';
        var percent = (typeof json.percent !== 'undefined') ? json.percent : 0;
        var minstudents = (typeof json.minstudents !== 'undefined') ? json.minstudents : 0;
        var activities = json.activities || [];
        var root = Y.Node.create('<div class="availability_classmetrics"></div>');
        // Nomes únicos para evitar conflitos entre múltiplas instâncias.
        var ruleName = Y.guid();
        var aggName  = Y.guid();
        root.setAttribute('data-rule', ruleName);
        root.setAttribute('data-agg', aggName);
        // Regra
        var ruleWrap = Y.Node.create('<div class="form-group"></div>');
        ruleWrap.append('<label>' + (strings.rule || 'Regra') + '</label><br>');
        ruleWrap.append('<label><input type="radio" name="' + ruleName + '" value="percent"' + (rule==='percent'?' checked':'') + '> ' + (strings.rule_percent || '% de conclusão (turma)') + '</label> ');
        ruleWrap.append('<label><input type="radio" name="' + ruleName + '" value="minstudents"' + (rule==='minstudents'?' checked':'') + '> ' + (strings.rule_minstudents || 'Nº mínimo de alunos (turma)') + '</label>');
        root.append(ruleWrap);
        // Grupo
        var groupWrap = Y.Node.create('<div class="form-group"></div>');
        groupWrap.append('<label>' + (strings.group || 'Grupo') + '</label><br>');
        var gsel = Y.Node.create('<select class="custom-select"></select>');
        Y.Array.each(this.groups, function(g) {
            var opt = Y.Node.create('<option></option>');
            opt.setAttribute('value', g.id);
            opt.setHTML(Y.Escape.html(g.name));
            if (groupid == g.id) { opt.setAttribute('selected', 'selected'); }
            gsel.append(opt);
        });
        groupWrap.append(gsel);
        root.append(groupWrap);
        // Bloco "percent"
        var percentWrap = Y.Node.create('<div class="form-group percentblock"' + (rule==='percent'?'':' style="display:none"') + '></div>');
        percentWrap.append('<label>' + (strings.activities || 'Atividades-alvo') + '</label><br>');
        var acts = Y.Node.create('<select multiple size="6" class="custom-select"></select>');
        Y.Array.each(this.cms, function(cm) {
            var opt = Y.Node.create('<option></option>');
            opt.setAttribute('value', cm.id);
            opt.setHTML(Y.Escape.html(cm.name));
            if (Y.Array.indexOf(activities, cm.id) !== -1) { opt.setAttribute('selected', 'selected'); }
            acts.append(opt);
        });
        percentWrap.append(acts);
        var aggWrap = Y.Node.create('<div class="mt-2"></div>');
        aggWrap.append('<label>' + (strings.aggregation || 'Agregação entre atividades') + '</label><br>');
        aggWrap.append('<label><input type="radio" name="' + aggName + '" value="all"' + (aggregation==='all'?' checked':'') + '> ' + (strings.aggregation_all || 'TODAS') + '</label> ');
        aggWrap.append('<label><input type="radio" name="' + aggName + '" value="any"' + (aggregation==='any'?' checked':'') + '> ' + (strings.aggregation_any || 'QUALQUER') + '</label>');
        percentWrap.append(aggWrap);
        var pWrap = Y.Node.create('<div class="mt-2"></div>');
        pWrap.append('<label>' + (strings.percent || 'Percentual mínimo (%)') + '</label><br>');
        var pin = Y.Node.create('<input type="number" class="form-control" min="0" max="100">');
        pin.set('value', percent);
        pWrap.append(pin);
        percentWrap.append(pWrap);
        root.append(percentWrap);
        // Bloco "minstudents"
        var minWrap = Y.Node.create('<div class="form-group minblock"' + (rule==='minstudents'?'':' style="display:none"') + '></div>');
        minWrap.append('<label>' + (strings.minstudents || 'Número mínimo de alunos') + '</label><br>');
        var minin = Y.Node.create('<input type="number" class="form-control" min="0">');
        minin.set('value', minstudents);
        minWrap.append(minin);
        root.append(minWrap);
        // Alternância
        root.one('input[name=' + ruleName + '][value=percent]').on('change', function() {
            percentWrap.setStyle('display', '');
            minWrap.setStyle('display', 'none');
        });
        root.one('input[name=' + ruleName + '][value=minstudents]').on('change', function() {
            percentWrap.setStyle('display', 'none');
            minWrap.setStyle('display', '');
        });
        return root;
    };
    // Serializa os valores para o JSON que o Moodle salva.
    M.availability_classmetrics.form.fillValue = function(value, node) {
        // Identificador do plugin no JSON salvo.
        value.type = 'classmetrics';
        var ruleName = node.getAttribute('data-rule');
        var rule = node.one('input[name=' + ruleName + ']:checked').get('value');
        value.rule = rule;
        // 1º <select> é o de grupo (o de atividades está no bloco percent).
        value.groupid = parseInt(node.one('.form-group select').get('value'), 10) || 0;
        if (rule === 'percent') {
            var acts = [];
            node.all('.percentblock select option').each(function(opt){
                if (opt.get('selected')) { acts.push(parseInt(opt.get('value'), 10)); }
            });
            value.activities = acts;
            var aggName = node.getAttribute('data-agg');
            value.aggregation = node.one('input[name=' + aggName + ']:checked').get('value') === 'any' ? 'any' : 'all';
            var p = parseInt(node.one('.percentblock input[type=number]').get('value'), 10);
            value.percent = isNaN(p) ? 0 : Math.max(0, Math.min(100, p));
        } else {
            delete value.activities;
            delete value.aggregation;
            delete value.percent;
            var m = parseInt(node.one('.minblock input[type=number]').get('value'), 10);
            value.minstudents = isNaN(m) ? 0 : Math.max(0, m);
        }
    };
    // Client-side validation.
    M.availability_classmetrics.form.fillErrors = function(errors, node) {
    var ruleName = node.getAttribute('data-rule');
    var rule = node.one('input[name=' + ruleName + ']:checked').get('value');
    if (rule === 'percent') {
        var selectedActs = 0;
        node.all('.percentblock select option').each(function(opt){
            if (opt.get('selected')) { selectedActs++; }
        });
        if (selectedActs === 0) {
            errors.push('availability_classmetrics:error_noactivities');
        }
        var p = parseInt(node.one('.percentblock input[type=number]').get('value'), 10);
        if (isNaN(p) || p < 0 || p > 100) {
            errors.push('availability_classmetrics:error_percent');
        }
    } else {
        var m = parseInt(node.one('.minblock input[type=number]').get('value'), 10);
        if (isNaN(m) || m < 0) {
            errors.push('availability_classmetrics:error_minstudents');
        }
    }
    // Sanitiza: remove undefined/strings vazias por segurança.
    for (var i = errors.length - 1; i >= 0; i--) {
        if (!errors[i] || typeof errors[i] !== 'string') {
            errors.splice(i, 1);
        }
    }
};

M.core_availability.form.addPlugin(M.availability_classmetrics.form);

}, '1.0.0', { requires: ['base', 'node', 'event', 'moodle-core_availability-form'] });

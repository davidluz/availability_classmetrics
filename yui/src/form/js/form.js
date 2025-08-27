/**
 * JavaScript for form editing class metrics conditions.
 *
 * @module moodle-availability_classmetrics-form
 */
M.availability_classmetrics = M.availability_classmetrics || {};

/**
 * @class M.availability_classmetrics.form
 * @extends M.core_availability.plugin
 */
M.availability_classmetrics.form = Y.Object(M.core_availability.plugin);

M.availability_classmetrics.form.activities = null;
M.availability_classmetrics.form.groups = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} params Array of parameters
 */
M.availability_classmetrics.form.initInner = function(params) {
    this.activities = params[0] || [];
    this.groups = params[1] || [];
};

/**
 * Gets the numeric value from a field.
 *
 * @method getValue
 * @param {String} field Field name
 * @return {Number|String} Value
 */
M.availability_classmetrics.form.getValue = function(field) {
    var node = this.getRoot().one('[name=' + field + ']');
    return node ? node.get('value') : '';
};

/**
 * Sets the value of a field.
 *
 * @method setValue
 * @param {String} field Field name
 * @param {String} value Value to set
 */
M.availability_classmetrics.form.setValue = function(field, value) {
    var node = this.getRoot().one('[name=' + field + ']');
    if (node) {
        node.set('value', value);
    }
};

/**
 * Gets the condition for saving.
 *
 * @method getCondition
 * @return {Object} Condition object
 */
M.availability_classmetrics.form.getCondition = function() {
    var condition = {
        type: 'classmetrics'
    };

    // Get condition type
    var conditionType = this.getValue('conditiontype');
    condition.conditiontype = conditionType;

    if (conditionType === 'completion') {
        condition.percentage = parseInt(this.getValue('percentage'), 10) || 0;

        // Get selected activities
        var selectedActivities = [];
        this.getRoot().all('input[name="activities[]"]:checked').each(function(node) {
            selectedActivities.push(node.get('value'));
        });
        condition.activities = selectedActivities.join(',');

    } else if (conditionType === 'students') {
        condition.minimum = parseInt(this.getValue('minimum'), 10) || 0;
    }

    // Get group filter
    var groupid = this.getValue('groupid');
    if (groupid && groupid !== '0') {
        condition.groupid = parseInt(groupid, 10);
    }

    return condition;
};

/**
 * Fills the form fields.
 *
 * @method fillValue
 * @param {Object} value Current value
 * @param {String} componentName Name of component
 */
M.availability_classmetrics.form.fillValue = function(value, componentName) {
    // Fill condition type
    if (value.conditiontype) {
        this.setValue('conditiontype', value.conditiontype);
        this.updateVisibility();
    }

    // Fill percentage
    if (value.percentage !== undefined) {
        this.setValue('percentage', value.percentage);
    }

    // Fill minimum students
    if (value.minimum !== undefined) {
        this.setValue('minimum', value.minimum);
    }

    // Fill selected activities
    if (value.activities) {
        var activities = value.activities.split(',');
        var root = this.getRoot();
        activities.forEach(function(activityId) {
            var checkbox = root.one('input[name="activities[]"][value="' + activityId + '"]');
            if (checkbox) {
                checkbox.set('checked', true);
            }
        });
    }

    // Fill group
    if (value.groupid !== undefined) {
        this.setValue('groupid', value.groupid);
    }
};

/**
 * Updates visibility of form sections based on condition type
 */
M.availability_classmetrics.form.updateVisibility = function() {
    var conditionType = this.getValue('conditiontype');
    var root = this.getRoot();

    var completionSection = root.one('.completion-section');
    var studentsSection = root.one('.students-section');

    if (completionSection) {
        completionSection.setStyle('display', conditionType === 'completion' ? 'block' : 'none');
    }
    if (studentsSection) {
        studentsSection.setStyle('display', conditionType === 'students' ? 'block' : 'none');
    }
};

/**
 * Gets the HTML for the form.
 *
 * @method getNode
 * @param {Object} json JSON from server
 * @return {Node} HTML node for form
 */
M.availability_classmetrics.form.getNode = function(json) {
    var html = '<div class="availability-classmetrics-form">';

    // Condition type selection
    html += '<div class="form-group">';
    html += '<label for="availability_classmetrics_conditiontype">' + M.util.get_string('title', 'availability_classmetrics') + ':</label>';
    html += '<select name="conditiontype" id="availability_classmetrics_conditiontype" class="form-control">';
    html += '<option value="completion">' + M.util.get_string('completion_percentage', 'availability_classmetrics') + '</option>';
    html += '<option value="students">' + M.util.get_string('minimum_students', 'availability_classmetrics') + '</option>';
    html += '</select>';
    html += '</div>';

    // Completion percentage section
    html += '<div class="completion-section" style="margin-top: 10px;">';
    html += '<div class="form-group">';
    html += '<label for="availability_classmetrics_percentage">Porcentagem (%):</label>';
    html += '<input name="percentage" type="number" min="0" max="100" class="form-control" id="availability_classmetrics_percentage" style="width: 100px; display: inline-block;">';
    html += '<span style="margin-left: 5px;">%</span>';
    html += '</div>';

    // Activities selection
    html += '<div class="form-group">';
    html += '<label>' + M.util.get_string('select_activities', 'availability_classmetrics') + ':</label>';
    html += '<div class="activities-list" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 5px;">';

    if (this.activities && this.activities.length > 0) {
        for (var i = 0; i < this.activities.length; i++) {
            var activity = this.activities[i];
            html += '<div class="checkbox">';
            html += '<label>';
            html += '<input type="checkbox" name="activities[]" value="' + activity.id + '"> ';
            html += activity.name + ' (' + activity.modname + ')';
            html += '</label>';
            html += '</div>';
        }
    } else {
        html += '<p><em>Nenhuma atividade com conclusão habilitada encontrada.</em></p>';
    }

    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Minimum students section
    html += '<div class="students-section" style="margin-top: 10px; display: none;">';
    html += '<div class="form-group">';
    html += '<label for="availability_classmetrics_minimum">Número mínimo de alunos:</label>';
    html += '<input name="minimum" type="number" min="1" class="form-control" id="availability_classmetrics_minimum" style="width: 100px;">';
    html += '</div>';
    html += '</div>';

    // Group filter section
    html += '<div class="form-group" style="margin-top: 10px;">';
    html += '<label for="availability_classmetrics_groupid">' + M.util.get_string('select_group', 'availability_classmetrics') + ':</label>';
    html += '<select name="groupid" id="availability_classmetrics_groupid" class="form-control">';
    html += '<option value="0">' + M.util.get_string('no_group', 'availability_classmetrics') + '</option>';

    if (this.groups && this.groups.length > 0) {
        for (var j = 0; j < this.groups.length; j++) {
            var group = this.groups[j];
            html += '<option value="' + group.id + '">' + group.name + '</option>';
        }
    }

    html += '</select>';
    html += '</div>';

    html += '</div>';

    var node = Y.Node.create(html);

    // Add event handlers
    var self = this;
    var updateForm = function() {
        if (M.core_availability && M.core_availability.form) {
            M.core_availability.form.update();
        }
    };

    node.one('select[name=conditiontype]').on('change', function() {
        self.updateVisibility();
        updateForm();
    });

    node.all('input, select').on('change', function() {
        updateForm();
    });

    // Set initial visibility
    setTimeout(function() {
        self.updateVisibility();
    }, 100);

    return node;
};

/**
 * Gets the error string (if any).
 *
 * @method getErrors
 * @param {Object} value Current value
 * @return {Array} Array of error strings
 */
M.availability_classmetrics.form.getErrors = function(value) {
    var errors = [];

    if (value.conditiontype === 'completion') {
        if (value.percentage === undefined || value.percentage < 0 || value.percentage > 100) {
            errors.push('availability_classmetrics:error_percentage');
        }
        if (!value.activities || value.activities === '') {
            errors.push('availability_classmetrics:error_activities');
        }
    } else if (value.conditiontype === 'students') {
        if (value.minimum === undefined || value.minimum < 1) {
            errors.push('availability_classmetrics:error_minimum');
        }
    }

    return errors;
};

// Register plugin with Moodle core availability form.
M.core_availability.form.registerPlugin('classmetrics', M.availability_classmetrics.form);



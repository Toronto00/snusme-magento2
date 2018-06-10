define([
    './abstract',
    'jquery',
    'underscore'
], function (Abstract, $, _) {
    return Abstract.extend({
        opacity:       .9,
        borderOpacity: 1,
        
        getChartConfig: function () {
            return {
                type:    'bar',
                options: {
                    title:               {
                        display: false
                    },
                    legend:              {
                        display:  true,
                        position: 'right',
                        onClick:  function (e, legendItem) {
                            var column = _.find(this.columns, {label: legendItem.text});
                            
                            column.isVisible = !column.isVisible;
                            
                            var index = legendItem.datasetIndex;
                            var meta = this.chart.getDatasetMeta(index);
                            meta.hidden = !column.isVisible;
                            
                            this.updateData();
                        }.bind(this)
                    },
                    responsive:          true,
                    maintainAspectRatio: false,
                    scales:              this.getScales(),
                    tooltips:            {
                        mode:      'index',
                        intersect: true
                    }
                }
            };
        },
        
        updateData: function () {
            if (!this.chart) {
                return;
            }
            
            var chart = this.chart;
            
            var data = {
                labels:   this.getLabels(),
                datasets: this.getDataSets()
            };
            
            if (this.chart.data !== data) {
                this.chart.data = data;
                this.chart.update(0, true);
                
                // dashed rectangle for comparison
                _.each(this.chart.data.datasets, function (set) {
                    if (set.xAxisID) {
                        if (set._meta[0]) {
                            _.each(set._meta[0].data, function (rectangle, index) {
                                rectangle.draw = function () {
                                    chart.chart.ctx.setLineDash([1, 1]);
                                    Chart.elements.Rectangle.prototype.draw.apply(this, arguments);
                                }
                            });
                        }
                    }
                });
            }
            
            this.updateScales();
        },
        
        updateScales: function () {
            _.each(this.scaleTypes, function (type) {
                var scale = _.find(this.chart.options.scales.yAxes, {id: 'scale-' + type});
                
                scale.display = _.findIndex(this.columns, {
                    type:      type,
                    isVisible: true
                }) >= 0;
            }.bind(this));
            
            this.chart.update(0, true);
        },
        
        getDataSets: function () {
            var sets = [];
            
            _.each(this.columns, function (column) {
                if (column.isInternal || _.indexOf(this.scaleTypes, column.type) === -1) {
                    return;
                }
                
                var set = {
                    label:           column.label,
                    stack:           column.index,
                    backgroundColor: Chart.helpers.color(column.color).alpha(this.opacity).rgbString(),
                    borderColor:     Chart.helpers.color(column.color).alpha(this.borderOpacity).rgbString(),
                    borderWidth:     1,
                    data:            [],
                    hidden:          !column.isVisible,
                    yAxisID:         'scale-' + column.type
                };
                var comparisonSet = {
                    label:           false,
                    stack:           column.index + '_c',
                    backgroundColor: Chart.helpers.color(column.color).alpha(.3).rgbString(),
                    borderColor:     Chart.helpers.color(column.color).alpha(1).rgbString(),
                    borderWidth:     1,
                    data:            [],
                    hidden:          !column.isVisible,
                    yAxisID:         'scale-' + column.type,
                    xAxisID:         'x-axis-c'
                };
                
                _.each(this.rows, function (row) {
                    var value = this.getCellValue(column, row);
                    set.data.push(value);
                    
                    if (this.getCellValue(column, row, 'c|')) {
                        comparisonSet.data.push(this.getCellValue(column, row, 'c|'));
                    }
                }, this);
                
                sets.push(set);
                
                if (comparisonSet.data.length) {
                    sets.push(comparisonSet);
                }
            }, this);
            
            return sets;
        },
        
        getScales: function () {
            var scales = {
                xAxes: [
                    {
                        display:   true,
                        id:        'x-axis',
                        stacked:   true,
                        gridLines: {
                            drawOnChartArea: false
                        }
                    },
                    {
                        display:            false,
                        stacked:            true,
                        id:                 "x-axis-c",
                        inside:             true,
                        type:               'category',
                        categoryPercentage: 0.8,
                        barPercentage:      0.9,
                        gridLines:          {
                            offsetGridLines: true
                        }
                    }
                ],
                yAxes: []
            };
            
            _.each(this.scaleTypes, function (type) {
                scales.yAxes.push({
                    display: true,
                    id:      'scale-' + type,
                    ticks:   {
                        beginAtZero: true
                    }
                });
            });
            
            return scales;
        }
    });
});
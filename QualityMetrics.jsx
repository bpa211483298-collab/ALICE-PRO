import React from 'react';

const QualityMetrics = ({ metrics }) => {
    const getScoreColor = (score) => {
        if (score >= 90) return 'text-green-600';
        if (score >= 70) return 'text-yellow-600';
        return 'text-red-600';
    };

    const getScoreIcon = (score) => {
        if (score >= 90) return '✅';
        if (score >= 70) return '⚠️';
        return '❌';
    };

    return (
        <div className="quality-metrics">
            <h3>Quality Assurance Report</h3>
            
            <div className="metrics-grid">
                <div className="metric-card">
                    <div className="metric-header">
                        <span>Security</span>
                        <span className={getScoreColor(metrics.security_score)}>
                            {getScoreIcon(metrics.security_score)} {metrics.security_score}/100
                        </span>
                    </div>
                    <div className="metric-issues">
                        {metrics.security_issues?.map((issue, index) => (
                            <div key={index} className="issue">• {issue}</div>
                        ))}
                    </div>
                </div>

                <div className="metric-card">
                    <div className="metric-header">
                        <span>Performance</span>
                        <span className={getScoreColor(metrics.performance_score)}>
                            {getScoreIcon(metrics.performance_score)} {metrics.performance_score}/100
                        </span>
                    </div>
                </div>

                <div className="metric-card">
                    <div className="metric-header">
                        <span>Accessibility</span>
                        <span className={getScoreColor(metrics.accessibility_score)}>
                            {getScoreIcon(metrics.accessibility_score)} {metrics.accessibility_score}/100
                        </span>
                    </div>
                    <div className="metric-wcag">
                        WCAG Level: {metrics.wcag_level}
                    </div>
                </div>

                <div className="metric-card">
                    <div className="metric-header">
                        <span>SEO</span>
                        <span className={getScoreColor(metrics.seo_score)}>
                            {getScoreIcon(metrics.seo_score)} {metrics.seo_score}/100
                        </span>
                    </div>
                </div>

                <div className="metric-card">
                    <div className="metric-header">
                        <span>Mobile</span>
                        <span className={getScoreColor(metrics.mobile_score)}>
                            {getScoreIcon(metrics.mobile_score)} {metrics.mobile_score}/100
                        </span>
                    </div>
                </div>
            </div>

            {metrics.recommendations && (
                <div className="recommendations">
                    <h4>Recommendations</h4>
                    <ul>
                        {metrics.recommendations.map((rec, index) => (
                            <li key={index}>{rec}</li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
};

export default QualityMetrics;
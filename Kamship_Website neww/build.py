#!/usr/bin/env python3
"""
Kamship Global website generator.

Edit CFG below (domain, email, phone, social links) and run:  python3 build.py
Every .html file in this folder is regenerated. Nothing else is touched.
"""
import json
import os

SITE = os.path.dirname(os.path.abspath(__file__))

CFG = {
    "brand":    "Kamship Global",
    "company":  "Kamship Pvt Ltd",
    "domain":   "https://kamship.com.np",
    "email":    "info@kamship.com.np",
    "phone_display": "+977 9856078508",
    "phone_link":    "+9779856078508",
    "whatsapp": "9779856078508",
    "city":     "Pokhara, Nepal",
    "address":  "Pokhara, Gandaki Province, Nepal",
    "linkedin": "https://www.linkedin.com/company/kamshipglobal",
    "facebook": "https://www.facebook.com/kamshipglobal",
    "jobs":     "https://kamship.com.np/jobs",
    "founded":  "2026",
}

# ---------------------------------------------------------------- icons ----
# Lucide-style 24x24 stroke icons, inlined so the page loads nothing external.
ICONS = {
    "shield":   '<path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
    "radar":    '<path d="M12 3a9 9 0 109 9"/><path d="M12 8a4 4 0 104 4"/><path d="M12 12l8-8"/>',
    "receipt":  '<path d="M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z"/><path d="M9 8h6"/><path d="M9 12h6"/>',
    "box":      '<path d="M3 8l9-5 9 5v8l-9 5-9-5z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/>',
    "file":     '<path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6"/><path d="M9 17h4"/>',
    "stamp":    '<path d="M5 21h14"/><path d="M8 17h8v-2a4 4 0 00-1.2-2.8A3 3 0 0114 10V7a2 2 0 10-4 0v3a3 3 0 01-.8 2.2A4 4 0 008 15z"/>',
    "calc":     '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8"/><path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>',
    "headset":  '<path d="M4 14v-2a8 8 0 0116 0v2"/><path d="M4 14a2 2 0 012-2h1v6H6a2 2 0 01-2-2z"/><path d="M20 14a2 2 0 00-2-2h-1v6h1a2 2 0 002-2z"/><path d="M17 18v1a3 3 0 01-3 3h-2"/>',
    "users":    '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.9"/><path d="M16 3.1a4 4 0 010 7.8"/>',
    "clock":    '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    "message":  '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
    "globe":    '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 010 18a15 15 0 010-18z"/>',
    "chart":    '<path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/>',
    "lock":     '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 118 0v3"/>',
    "truck":    '<path d="M3 16V6a1 1 0 011-1h10v11"/><path d="M14 9h4l3 3v4h-7"/><circle cx="7.5" cy="17.5" r="2"/><circle cx="17.5" cy="17.5" r="2"/>',
    "ship":     '<path d="M3 17l1.5-5h15L21 17"/><path d="M12 12V5"/><path d="M8 8h8"/><path d="M3 20c1.5 0 1.5-1 3-1s1.5 1 3 1 1.5-1 3-1 1.5 1 3 1 1.5-1 3-1"/>',
    "plane":    '<path d="M17.8 19.2L16 11l3.5-3.5a2.1 2.1 0 00-3-3L13 8 4.8 6.2a1 1 0 00-.9 1.7l6.3 4-2 4.3-2.6-.6a1 1 0 00-1 1.6l3 3 3 3a1 1 0 001.6-1l-.6-2.6 4.3-2 4 6.3a1 1 0 001.7-.9z"/>',
    "search":   '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
    "check":    '<path d="M20 6L9 17l-5-5"/>',
    "mail":     '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    "phone":    '<path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.2a2 2 0 012.1-.5c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z"/>',
    "pin":      '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1116 0z"/><circle cx="12" cy="10" r="3"/>',
    "spark":    '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/><path d="M18 16l.9 2.1L21 19l-2.1.9L18 22l-.9-2.1L15 19l2.1-.9z"/>',
    "layers":   '<path d="M12 2l9 5-9 5-9-5z"/><path d="M3 12l9 5 9-5"/><path d="M3 17l9 5 9-5"/>',
    "cycle":    '<path d="M3 12a9 9 0 0115-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
}


def icon(name, cls="", size=24):
    body = ICONS.get(name, ICONS["check"])
    return (f'<svg class="{cls}" viewBox="0 0 24 24" width="{size}" height="{size}" fill="none" '
            f'stroke="currentColor" stroke-width="1.7" stroke-linecap="round" '
            f'stroke-linejoin="round" aria-hidden="true">{body}</svg>')


ARROW = ('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" '
         'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>')

CHEV = ('<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" '
        'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>')

# ------------------------------------------------------------- services ----
SERVICES = [
    {
        "slug": "carrier-vetting",
        "navdesc": "Your checklist, run and evidenced every time",
        "icon": "shield",
        "nav": "Carrier vetting &amp; onboarding",
        "title": "Carrier vetting and onboarding",
        "blurb": "Your written checklist run on every new carrier and every pre-tender recheck, with each step logged with its source and time. Anything that fails goes to your approver.",
        "tags": ["Authority checks", "Insurance verification", "Fraud red flags", "Evidence file"],
        "lead": "Repetitive, exacting checks that must be identical every time and leave a record. That is exactly what a supervised desk does well.",
        "what": [
            ("Operating authority", "Status, age and history verified in FMCSA records, checked against the details the carrier gave you."),
            ("Insurance verification", "Confirmed with the insurer or agent directly, not just read off a certificate, with coverage limits checked against your minimums."),
            ("Safety review", "Safety scores and inspection history checked against the thresholds you set, with anything borderline flagged rather than judged."),
            ("Identity matching", "Email domain, phone and address matched to registered records, and the registered number called to confirm the carrier is who they claim."),
            ("Fraud screening", "Checks against the fraud databases and watch lists your business subscribes to, plus double-brokering red flags."),
            ("Document collection", "W-9, signed carrier packet, authority letter and certificates gathered through your onboarding platform and filed where your team expects them."),
            ("The evidence file", "Every check recorded with source, timestamp and a screenshot or export, so you can produce the file when someone asks for it."),
        ],
        "targets": [
            ("Checklist steps completed with evidence", "100%"),
            ("Time per new carrier file", "Under 30 minutes"),
            ("Pre-tender rechecks completed before dispatch", "100%"),
            ("Exceptions escalated to your approver", "Within 15 minutes"),
        ],
        "tools": "Highway, Carrier Assure, RMIS, MyCarrierPackets, Truckstop, DAT, FMCSA SAFER and your own TMS",
        "note": "This is an operational service, not legal advice, and it does not by itself make a broker compliant or protect against liability. Your team sets the policy and approves every carrier.",
    },
    {
        "slug": "track-and-trace",
        "navdesc": "Visibility kept current through the night",
        "icon": "radar",
        "nav": "Track and trace",
        "title": "Track and trace",
        "blurb": "Shipment visibility kept current overnight and through the weekend: app and ELD tracking first, check calls for exceptions, and your TMS and customer portals updated.",
        "tags": ["Check calls", "TMS updates", "Portal updates", "Exception escalation"],
        "lead": "Your customers judge you on whether the status is right when they look. We keep it right while your team is asleep.",
        "what": [
            ("Tracking setup", "Tracking requested and confirmed on every load at dispatch, through whichever app or ELD integration you use."),
            ("Milestone updates", "Pickup, in transit, delivered and any change logged in your TMS as it happens, not batched up at the end of a shift."),
            ("Check calls", "Calls and messages to drivers and dispatchers on the loads that need them, following your cadence rules."),
            ("Customer portal updates", "Status pushed into shipper and broker portals so your customer sees the same picture as your TMS."),
            ("Exception handling", "Late, stopped or off-route loads escalated to your on-call person within minutes, with what we already know attached."),
            ("Appointment management", "Delivery appointments booked, moved and confirmed in receiver portals, with the new times recorded."),
            ("End-of-shift handover", "A written handover at the end of every shift so your morning team starts with a clear picture."),
        ],
        "targets": [
            ("Tracking update compliance", "98% or better"),
            ("At-risk loads escalated", "Within 10 minutes"),
            ("Loads with a confirmed tracking method at dispatch", "97%"),
            ("Shift handover delivered", "Every shift, in writing"),
        ],
        "tools": "Macropoint, Trucker Tools, project44, FourKites, McLeod, Aljex, Turvo, Tai, Rose Rocket",
        "note": "",
    },
    {
        "slug": "pod-billing",
        "navdesc": "Paperwork chased, checked and invoiced",
        "icon": "receipt",
        "nav": "POD, billing &amp; carrier pay",
        "title": "POD, billing and carrier pay",
        "blurb": "Delivery paperwork chased, checked and turned into invoices overnight, so your cash cycle does not wait for the morning shift.",
        "tags": ["POD collection", "Invoice preparation", "Carrier pay matching", "Accessorial checks"],
        "lead": "Every day a POD sits uncollected is a day your invoice is not out and your cash is not in. This desk closes that gap while your office is closed.",
        "what": [
            ("POD collection", "Delivery paperwork chased from drivers, carriers and receivers until it arrives, then filed against the load."),
            ("Document validation", "POD checked against the bill of lading and rate confirmation: quantities, signatures, dates, damage notes and any exception written on the paperwork."),
            ("Billing packets", "Complete packets assembled to your customer's requirements, including whatever supporting documents each shipper demands."),
            ("Invoicing", "Invoices raised in your TMS or accounting system from the agreed rate, with accessorials applied per your rules."),
            ("Carrier pay matching", "Carrier invoices matched to rate confirmations, differences identified and queued for your approval rather than paid."),
            ("Accessorial review", "Detention, layover, lumper and other charges checked against documentation before they reach your customer or your payable."),
            ("Aging follow-up", "Unpaid invoices and missing paperwork chased on the schedule you set, with a weekly exception list."),
        ],
        "targets": [
            ("POD collected after delivery", "95% within 24 hours"),
            ("Invoiced after POD received", "98% within 24 hours"),
            ("Billing accuracy", "99% after month three"),
            ("Carrier invoice discrepancies flagged, not paid", "100%"),
        ],
        "tools": "McLeod, Aljex, Tai, Turvo, QuickBooks, Xero, NetSuite and your document management system",
        "note": "We prepare and match. Payment release and credit decisions stay with your team.",
    },
    {
        "slug": "load-entry",
        "navdesc": "Orders, rate confirmations and appointments",
        "icon": "box",
        "nav": "Load entry &amp; appointments",
        "title": "Load entry and appointments",
        "blurb": "Orders built accurately in your TMS, rate confirmations sent from agreed rates, and appointments booked in shipper and receiver portals.",
        "tags": ["Order entry", "Rate confirmations", "Appointment booking", "Data cleanup"],
        "lead": "The unglamorous keystrokes that decide whether everything downstream is right. Done carefully, by people who do nothing else.",
        "what": [
            ("Order entry", "Loads built in your TMS from customer emails, EDI exceptions, portals or spreadsheets, with every required field completed."),
            ("Rate confirmations", "Confirmations generated and sent from the rate your team agreed, then tracked until signed and returned."),
            ("Appointment booking", "Pickup and delivery appointments requested, confirmed and recorded in shipper and receiver portals."),
            ("Carrier packet chasing", "Signed rate confirmations and required documents chased before dispatch."),
            ("Data cleanup", "Duplicate records merged, missing fields completed, lane and customer data tidied so your reporting is worth reading."),
            ("Quote entry", "Customer quote requests entered and tracked so nothing waits in an inbox overnight."),
        ],
        "targets": [
            ("Order entry accuracy", "99%"),
            ("Loads entered within agreed turnaround", "98%"),
            ("Rate confirmations sent after booking", "Within 15 minutes"),
            ("Appointment confirmations recorded", "100%"),
        ],
        "tools": "McLeod, Aljex, Tai, Turvo, Revenova, Rose Rocket, plus shipper and receiver portals",
        "note": "",
    },
    {
        "slug": "forwarding-documentation",
        "navdesc": "Job files, house bills and arrival notices",
        "icon": "file",
        "nav": "Forwarding documentation",
        "title": "Freight forwarding documentation",
        "blurb": "Shipment files, house bills, arrival notices and pre-alerts prepared during your working day, in CargoWise, Magaya or your own system.",
        "tags": ["CargoWise", "HBL and HAWB", "Arrival notices", "Job costing"],
        "lead": "Documentation work that has to be right the first time, handled by a team trained on your system and working your hours.",
        "what": [
            ("Shipment file creation", "Booking details turned into a complete job file, with parties, routing, containers and commodity detail entered correctly."),
            ("House bill drafting", "HBL and HAWB drafted and checked against the commercial invoice, packing list and booking confirmation before they reach you."),
            ("Pre-alerts and arrival notices", "Pre-alerts issued to agents and arrival notices to consignees on your template and schedule."),
            ("Milestone updates", "Sailing, transhipment, arrival and delivery milestones kept current so your customers stop calling to ask."),
            ("Container tracking", "Container status monitored with demurrage and detention risk flagged before charges land."),
            ("Rate and tariff maintenance", "Rate sheets and tariffs loaded and kept current in your system."),
            ("Job costing", "Costs accrued against the job, supplier invoices matched, and the file readied for invoicing and closure."),
        ],
        "targets": [
            ("Document accuracy before your review", "99%"),
            ("Job files created within turnaround", "98%"),
            ("Arrival notices issued on schedule", "100%"),
            ("D&amp;D risk flagged before free time expires", "100%"),
        ],
        "tools": "CargoWise, Magaya, Descartes, Softlink, and carrier and port portals",
        "note": "",
    },
    {
        "slug": "customs-data",
        "navdesc": "Entry data prepared for your licensed broker",
        "icon": "stamp",
        "nav": "Customs data preparation",
        "title": "Customs data preparation",
        "blurb": "Entry data assembled, classified against your own tariff database and checked for completeness, ready for your licensed broker to review and file.",
        "tags": ["Entry data", "Document checks", "ISF preparation", "Broker-ready"],
        "lead": "Preparation work that saves your licensed people from typing, so their time goes on judgement instead of data entry.",
        "what": [
            ("Document gathering", "Commercial invoice, packing list, bill of lading and certificates collected and checked for completeness against your entry checklist."),
            ("Data entry", "Entry data assembled in your customs software from source documents, with values, weights, quantities and parties verified against each other."),
            ("Classification support", "Line items matched to classifications in your own product and tariff database, with anything new or ambiguous referred to your licensed staff."),
            ("ISF and advance filings", "Advance filing data prepared within your timelines and queued for your filer."),
            ("Discrepancy flagging", "Mismatches between documents identified and raised before the entry reaches your reviewer."),
            ("Post-entry filing", "Entry packets filed and indexed so your records are retrievable when an audit asks."),
        ],
        "targets": [
            ("Entry data prepared within turnaround", "98%"),
            ("Documents complete before hand-off to your filer", "99%"),
            ("Discrepancies raised before review", "100%"),
        ],
        "tools": "CargoWise, Descartes, and your own customs filing software",
        "note": "Kamship staff are not licensed customs brokers and do not file entries, give classification rulings or advise on customs law. All work is prepared for review and filing by your own licensed broker, who remains responsible.",
    },
    {
        "slug": "freight-accounting",
        "navdesc": "AR, AP, reconciliation and load-level margin",
        "icon": "calc",
        "nav": "Freight accounting &amp; AR/AP",
        "title": "Freight accounting and AR/AP",
        "blurb": "Bookkeeping built around how freight actually bills: load-level margin, carrier payables, customer receivables and the reconciliations in between.",
        "tags": ["AR and collections", "AP processing", "Reconciliation", "Margin reporting"],
        "lead": "Freight accounting is its own discipline. Our specialist desk works to your chart of accounts and your close calendar.",
        "what": [
            ("Accounts receivable", "Invoices issued, statements sent, and aging worked on your schedule, with a weekly list of what needs your intervention."),
            ("Collections support", "Polite, persistent follow-up on overdue invoices following your escalation ladder, with every contact logged."),
            ("Accounts payable", "Carrier and vendor invoices coded, matched and queued for approval, with duplicates and overcharges caught before payment."),
            ("Reconciliation", "Bank, factoring and carrier account reconciliations completed and the differences explained."),
            ("Load-level margin", "Revenue and cost recorded against each load so your margin reporting reflects reality."),
            ("Month-end support", "Accruals, schedules and supporting workbooks prepared for your accountant's close."),
        ],
        "targets": [
            ("Invoices issued within agreed turnaround", "98%"),
            ("AP coding accuracy", "99%"),
            ("Reconciliations completed by the agreed working day", "100%"),
            ("Aging report delivered", "Weekly"),
        ],
        "tools": "QuickBooks, Xero, NetSuite, Sage, McLeod accounting, and factoring portals",
        "note": "We prepare and reconcile. Payment approval, tax filing and financial sign-off stay with your team and your accountant.",
    },
    {
        "slug": "after-hours-support",
        "navdesc": "Night and weekend cover on your rules",
        "icon": "headset",
        "nav": "After-hours support",
        "title": "After-hours and weekend support",
        "blurb": "Someone who knows your process answering carriers, drivers and customers at 2am, following your escalation rules and waking the right person only when it matters.",
        "tags": ["Night coverage", "Weekend cover", "Escalation rules", "Shift reports"],
        "lead": "Most after-hours messages need an answer, not a decision. We handle the answers and escalate the decisions.",
        "what": [
            ("Inbox and queue coverage", "Your shared inbox, dispatch queue or ticket system worked continuously through the night and weekend."),
            ("Carrier and driver calls", "Calls answered in your company's name by staff who pass a spoken-English assessment and who you interview before they start."),
            ("Escalation discipline", "A written rule for every situation: what we handle, what we log for the morning, and what warrants a call to your on-call person."),
            ("Emergency coordination", "Breakdowns, accidents, refusals and detention coordinated under your instructions, with everything documented as it happens."),
            ("Shift reporting", "A written report at the end of every shift: what was handled, what was escalated and what your morning team needs to pick up."),
            ("Holiday and weekend cover", "Cover planned around your calendar, including US holidays when your office is closed but freight still moves."),
        ],
        "targets": [
            ("First response to inbound message", "Within 15 minutes"),
            ("Escalation rules followed", "100%, audited weekly"),
            ("Shift report delivered", "Every shift"),
            ("Seat availability including backup cover", "97%"),
        ],
        "tools": "Your phone system, shared inbox, TMS and ticketing tools. We work in your accounts, not ours.",
        "note": "",
    },
]

INDUSTRIES = [
    ("Freight brokers and 3PLs", "truck",
     "Overnight coverage of the whole load lifecycle: vetting, tracking, POD, billing and carrier pay, worked inside your TMS while your US office is closed.",
     ["Carrier vetting", "Track and trace", "POD and billing", "After-hours"]),
    ("Freight forwarders", "ship",
     "Documentation and job files prepared during your working day, whether you run on CargoWise, Magaya or something of your own.",
     ["Job files", "HBL and HAWB", "Arrival notices", "Job costing"]),
    ("Customs brokers", "stamp",
     "Entry data assembled and checked so your licensed people spend their time reviewing and filing, not typing.",
     ["Entry preparation", "Document checks", "ISF data", "Records filing"]),
    ("Shippers and manufacturers", "box",
     "In-house logistics teams supported with order entry, carrier coordination, freight bill checking and reporting.",
     ["Order entry", "Freight audit", "Reporting", "Carrier coordination"]),
    ("Freight audit and factoring", "chart",
     "High-volume document indexing, invoice auditing and data capture, with the accuracy standards those businesses live on.",
     ["Invoice audit", "Document indexing", "Data capture", "Exception handling"]),
    ("Air and ocean NVOCCs", "plane",
     "Booking desks, documentation and milestone management across time zones, with one team covering both the US night and the Asian morning.",
     ["Bookings", "Documentation", "Milestones", "Rate maintenance"]),
]

SYSTEMS = ["McLeod", "Aljex", "Tai", "Turvo", "CargoWise", "Magaya", "Descartes", "DAT",
           "Truckstop", "Highway", "RMIS", "MyCarrierPackets", "Macropoint", "Trucker Tools",
           "project44", "Rose Rocket", "Revenova", "QuickBooks", "Xero", "NetSuite"]

INSIGHTS = [
    {
        "slug": "carrier-vetting-after-montgomery",
        "title": "What the Montgomery ruling changed for broker back offices",
        "date": "2026-09-18",
        "date_display": "18 September 2026",
        "cat": "Carrier vetting",
        "excerpt": "In May 2026 the US Supreme Court held that state negligent-selection claims against freight brokers are not preempted by federal law. The operational consequence is simple: your vetting file now has to survive being read out loud.",
        "body": [
            ("h2", "What actually happened"),
            ("p", "On 14 May 2026 the Supreme Court decided <i>Montgomery v. Caribe Transport II</i>, holding that state-law negligent-selection claims against freight brokers are not preempted by the Federal Aviation Administration Authorization Act. Brokers can now face those claims in state court in circumstances where a preemption defence previously ended the matter early."),
            ("p", "We are an operations company, not a law firm, and nothing here is legal advice. Your counsel should tell you what the ruling means for your risk. What we can talk about is the operational consequence, because that is where the work lands."),
            ("h2", "The operational consequence"),
            ("p", "If a carrier selection decision can be examined years later, the question stops being \"did you have a policy?\" and becomes \"can you show what you did on this particular carrier, on this particular day?\" Those are very different requirements. The first needs a document. The second needs a discipline."),
            ("blockquote", "A vetting policy nobody can evidence is, in practice, a vetting policy nobody followed."),
            ("p", "Most brokers we speak to already have a sound checklist. What they do not always have is proof that the checklist ran identically on the Tuesday afternoon when dispatch was three loads behind and one person was covering two desks."),
            ("h2", "What a defensible file contains"),
            ("ul", [
                "The check performed, named the same way every time",
                "The source consulted, with a screenshot or export attached",
                "The timestamp, and who performed the check",
                "The result, including the ones that passed unremarkably",
                "The exception path: what failed, who was told, what they decided",
            ]),
            ("p", "That last line matters more than people expect. A file showing that a problem was found, escalated and knowingly accepted by an authorised person tells a much better story than a file with no record of the problem at all."),
            ("h2", "Why this work suits a supervised desk"),
            ("p", "Vetting is repetitive, rule-bound and exacting, and it has to happen at the least convenient moments. Those are exactly the conditions under which an in-house team, pressed for time, starts taking shortcuts that nobody intends and nobody notices."),
            ("p", "A dedicated desk removes the time pressure from the checklist. The people doing it have no competing dispatch duty, they run the same steps in the same order every time, and a QA reviewer samples their files daily. The broker keeps every approval decision; the desk keeps the record."),
            ("h2", "Where to start"),
            ("p", "Write the checklist down as a numbered procedure, including the escalation path. Decide what evidence each step needs. Then run it on fifty carriers and see whether the files look the same. If they do not, the problem is capacity, and capacity is a solvable problem."),
        ],
    },
    {
        "slug": "why-nepal-freight-back-office",
        "title": "Why a freight back office in Nepal works",
        "date": "2026-09-11",
        "date_display": "11 September 2026",
        "cat": "Offshore operations",
        "excerpt": "Nepal's time zone covers the US night and the Gulf, European and Asian working day from one centre. That is a real advantage. It is also not the whole story, and it is worth being clear about the trade-offs.",
        "body": [
            ("h2", "The time zone argument"),
            ("p", "Nepal runs at UTC+05:45. Our late evening is the American working day, and our working day covers the Gulf, most of Europe and much of Asia-Pacific. One centre, in one building, under one management structure, can therefore cover a US brokerage night desk and a forwarding day desk without either team working a shift nobody wants."),
            ("p", "For a US broker this means the overnight queue is worked by people who are awake, supervised and on their normal schedule, rather than by someone at home fighting sleep."),
            ("h2", "The staffing argument"),
            ("p", "Around nine in ten of Nepal's IT and outsourcing jobs sit in Kathmandu. Pokhara, the country's second city, has universities, a steady supply of graduates and far less competition for them. In practice that means lower attrition, which for a client translates directly into a team that still knows their process in month nine."),
            ("p", "Attrition is the hidden cost of offshore work. A cheap seat that turns over every five months is not cheap; you pay for it in retraining, errors and the quiet erosion of everything you documented."),
            ("h2", "Where Nepal is not the answer"),
            ("ul", [
                "<b>Licensed work.</b> Customs entries, brokerage authority decisions, legal and tax sign-off must stay with your licensed people. We prepare; we do not file or decide.",
                "<b>Heavy outbound sales calling.</b> Some accents suit some markets. We will tell you plainly if a voice-heavy role is a poor fit rather than sell you seats that disappoint.",
                "<b>Very small volumes.</b> Below about two full-time seats, the supervision and QA structure that makes this work is not worth its overhead to you.",
                "<b>Undefined processes.</b> If the work exists only in one person's head, offshoring it will fail. Document it first; we will help, but it has to happen.",
            ]),
            ("h2", "The infrastructure question"),
            ("p", "The fair question is whether Nepal can keep the lights on and the internet up. The honest answer is that a centre has to plan for both. Ours runs two independent fibre providers on separate routes and backup power sized for the full floor. Any provider who does not want to discuss this in detail is a provider to avoid."),
            ("h2", "The festival question"),
            ("p", "Dashain and Tihar fall in October and November and matter enormously here. Any serious provider publishes the holiday calendar before you sign and schedules cover in advance. Being surprised by Dashain is a planning failure, not a cultural one."),
        ],
    },
    {
        "slug": "first-process-to-outsource",
        "title": "Choosing the first freight process to hand over",
        "date": "2026-09-04",
        "date_display": "4 September 2026",
        "cat": "Getting started",
        "excerpt": "The first process you outsource decides whether the second one ever happens. Pick for clarity, not for volume, and start smaller than feels worthwhile.",
        "body": [
            ("h2", "The trap"),
            ("p", "The instinct is to hand over the biggest pain first, because that is where the relief would be greatest. The problem is that the biggest pain is usually the messiest process, and messy processes are the ones that fail an offshore handover for reasons that have nothing to do with the team doing them."),
            ("h2", "Four tests for a good first process"),
            ("ul", [
                "<b>It can be written down.</b> If you can describe it as numbered steps with clear decision rules, it can be taught. If every case is a judgement call, it cannot, yet.",
                "<b>Right and wrong are visible.</b> POD collected or not. Field entered correctly or not. Processes where quality is arguable make a pilot impossible to assess honestly.",
                "<b>It repeats.</b> At least a few dozen times a week, so patterns emerge fast and training pays for itself inside the pilot.",
                "<b>Failure is recoverable.</b> Not the process where one mistake loses the customer. Build trust on ground where an error costs an apology, not an account.",
            ]),
            ("h2", "What usually fits"),
            ("p", "For brokers: POD collection and billing preparation, carrier packet chasing, appointment booking, and documented carrier vetting. For forwarders: job file creation, arrival notices, and milestone updates. These are high volume, checkable, and the cost of a caught error is small."),
            ("p", "What usually does not fit first: rate negotiation, customer escalation handling, anything requiring a licence, and anything where the procedure lives in one experienced person's memory."),
            ("h2", "Start smaller than feels worthwhile"),
            ("p", "Two seats on one process for two weeks tells you almost everything you need to know: whether the training holds, whether the QA is real, whether the shift reports are useful, and whether the communication works across the time difference. It costs about the same as a week of a US hire and it is reversible."),
            ("h2", "Write the SOP together"),
            ("p", "The single best predictor of a successful handover is whether the client and the provider wrote the procedure together. Not a provider guessing from a recorded call, and not a client sending a document written for someone who already knows the job. Sit down, walk it through, write it, and argue about the edge cases before they cost anything."),
        ],
    },
]

# ------------------------------------------------------------ navigation ---
NAV = [
    ("index.html", "Home", None),
    ("services.html", "Services", [(f"service-{s['slug']}.html", s["nav"], s["navdesc"]) for s in SERVICES]),
    ("industries.html", "Industries", None),
    ("about.html", "Company", [
        ("about.html", "About Kamship", "Who we are and how we are set up"),
        ("why-nepal.html", "Why Nepal", "Time zone, talent and infrastructure"),
        ("security.html", "Security &amp; data", "How we protect client systems and data"),
        ("pilot.html", "How the pilot works", "Two weeks, one process, agreed targets"),
    ]),
    ("pricing.html", "Pricing", None),
    ("careers.html", "Careers", None),
    ("insights.html", "Insights", None),
]


# ----------------------------------------------------------- chrome bits ---
def hero_bg(seed=0):
    """Animated route network. Pure SVG, no images, no external requests."""
    import random
    rnd = random.Random(seed)
    paths, dots = [], []
    for i in range(9):
        y1 = rnd.uniform(6, 94)
        y2 = rnd.uniform(6, 94)
        cy = min(y1, y2) - rnd.uniform(10, 28)
        d = f"M-4,{y1:.1f} Q50,{cy:.1f} 104,{y2:.1f}"
        paths.append(
            f'<path d="{d}" stroke="#CBD5E1" stroke-opacity="0.18" stroke-width="0.22" fill="none" '
            f'vector-effect="non-scaling-stroke"/>'
            f'<path d="{d}" stroke="#38BDF8" stroke-opacity="0.5" stroke-width="0.3" fill="none" '
            f'stroke-dasharray="3 60" vector-effect="non-scaling-stroke">'
            f'<animate attributeName="stroke-dashoffset" from="63" to="0" '
            f'dur="{rnd.uniform(7, 14):.1f}s" repeatCount="indefinite"/></path>'
        )
    for i in range(14):
        x = rnd.uniform(3, 97)
        y = rnd.uniform(4, 96)
        amber = i % 4 == 0
        r = 0.55 if amber else 0.36
        col = "#E8A317" if amber else "#CBD5E1"
        op = 0.85 if amber else 0.32
        dots.append(
            f'<circle cx="{x:.1f}" cy="{y:.1f}" r="{r}" fill="{col}" fill-opacity="{op}">'
            f'<animate attributeName="fill-opacity" values="{op};{op*0.35:.2f};{op}" '
            f'dur="{rnd.uniform(3.5, 7):.1f}s" repeatCount="indefinite"/></circle>'
        )
    return ('<div class="hero-bg" aria-hidden="true">'
            '<svg viewBox="0 0 100 100" preserveAspectRatio="none" role="presentation">'
            + "".join(paths) + "".join(dots) + "</svg></div>")


def head(title, desc, path, schema=None, extra=""):
    url = CFG["domain"] + "/" + ("" if path == "index.html" else path)
    s = f"""<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<meta name="description" content="{desc}">
<link rel="canonical" href="{url}">
<meta name="robots" content="index, follow">
<meta property="og:type" content="website">
<meta property="og:site_name" content="{CFG['brand']}">
<meta property="og:title" content="{title}">
<meta property="og:description" content="{desc}">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{CFG['domain']}/assets/img/og-image-1200x630.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{title}">
<meta name="twitter:description" content="{desc}">
<meta name="twitter:image" content="{CFG['domain']}/assets/img/og-image-1200x630.png">
<meta name="theme-color" content="#0F172A">
<link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
<link rel="icon" href="assets/img/kamship-icon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="assets/img/kamship-icon-180.png">
<link rel="manifest" href="site.webmanifest">
<link rel="preload" href="assets/fonts/PlusJakartaSans-var.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="assets/css/style.css">
{extra}"""
    if schema:
        s += f'\n<script type="application/ld+json">{json.dumps(schema)}</script>'
    s += """
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="progress" aria-hidden="true"></div>
"""
    return s


def header(path):
    items = []
    for href, label, kids in NAV:
        active = ' aria-current="page"' if href == path else ""
        if kids:
            links = "".join(
                f'<a href="{k}"><strong>{t}</strong><span>{d}</span></a>' for k, t, d in kids
            )
            items.append(
                f'<li class="nav-item">'
                f'<button class="nav-toggle" type="button" aria-expanded="false">{label}{CHEV}</button>'
                f'<div class="dropdown">{links}</div></li>'
            )
        else:
            items.append(f'<li><a href="{href}"{active}>{label}</a></li>')
    nav_html = "".join(items)

    return f"""<div class="topbar">
  <div class="wrap">
    <span class="topbar-live"><span class="pulse-dot"></span> Desks live now &middot; Pokhara <time data-tz="Asia/Kathmandu">--:--</time></span>
    <span class="topbar-contact">
      <a href="mailto:{CFG['email']}">{CFG['email']}</a>
      <a href="tel:{CFG['phone_link']}">{CFG['phone_display']}</a>
    </span>
  </div>
</div>
<header class="site-header">
  <div class="wrap navbar">
    <a class="brand" href="index.html" aria-label="{CFG['brand']} home">
      <img src="assets/img/kamship-global-logo.svg" alt="{CFG['brand']}" width="300" height="38">
    </a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="burger" aria-hidden="true"><span></span><span></span><span></span></span> Menu
    </button>
    <nav id="site-nav" class="nav" aria-label="Main">
      <ul class="nav-list">{nav_html}</ul>
      <a class="btn btn-primary btn-sm" href="contact.html">Book a call</a>
    </nav>
  </div>
</header>
<div class="nav-scrim" aria-hidden="true"></div>
<main id="main">
"""


def cta(title="Start with one process and two weeks.",
        text="Tell us which process is stretched. We map it in your systems, agree the targets in writing, and run a paid two-week pilot. Miss the targets and you walk away."):
    return f"""<section class="cta-band on-dark">
  {hero_bg(99)}
  <div class="wrap">
    <div class="split center">
      <div data-reveal="left">
        <p class="eyebrow">Next step</p>
        <h2>{title}</h2>
        <p class="lead">{text}</p>
        <div class="actions">
          <a class="btn btn-amber" href="contact.html">Book a 20-minute call</a>
          <a class="btn btn-ghost" href="pilot.html">How the pilot works</a>
        </div>
      </div>
      <div data-reveal="right">
        <div class="contact-tile">
          <span class="card-icon">{icon('mail', size=22)}</span>
          <span><b>Email us</b><a href="mailto:{CFG['email']}">{CFG['email']}</a></span>
        </div>
        <div class="contact-tile">
          <span class="card-icon">{icon('phone', size=22)}</span>
          <span><b>Call or WhatsApp</b><a href="tel:{CFG['phone_link']}">{CFG['phone_display']}</a></span>
        </div>
        <div class="contact-tile">
          <span class="card-icon">{icon('clock', size=22)}</span>
          <span><b>We reply</b><span class="small">Within one business day, in your time zone</span></span>
        </div>
      </div>
    </div>
  </div>
</section>
"""


def footer():
    svc_links = "".join(f'<li><a href="service-{s["slug"]}.html">{s["nav"]}</a></li>' for s in SERVICES[:6])
    return f"""</main>
<footer class="site-footer">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <img class="foot-logo" src="assets/img/kamship-global-logo-reversed.svg" alt="{CFG['brand']}" width="280" height="36">
        <p>Supervised freight back-office teams for brokers, forwarders and customs brokers, delivered from {CFG['city']}.</p>
        <div class="social-row">
          <a href="{CFG['linkedin']}" rel="noopener" aria-label="Kamship Global on LinkedIn">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 11-.02 5 2.5 2.5 0 01.02-5zM3 8.98h4v12H3v-12zM9 8.98h3.8v1.64h.05c.53-1 1.83-2.05 3.76-2.05C20.4 8.57 21 11.1 21 14.2v6.78h-4v-6c0-1.43-.03-3.27-2-3.27-2 0-2.3 1.56-2.3 3.17v6.1H9v-12z"/></svg>
          </a>
          <a href="{CFG['facebook']}" rel="noopener" aria-label="Kamship Global on Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"/></svg>
          </a>
          <a href="mailto:{CFG['email']}" aria-label="Email Kamship Global">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          </a>
        </div>
      </div>
      <div>
        <h4>Services</h4>
        <ul>{svc_links}<li><a href="services.html">All services</a></li></ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="about.html">About us</a></li>
          <li><a href="why-nepal.html">Why Nepal</a></li>
          <li><a href="security.html">Security &amp; data</a></li>
          <li><a href="pilot.html">How the pilot works</a></li>
          <li><a href="pricing.html">Pricing</a></li>
          <li><a href="careers.html">Careers</a></li>
          <li><a href="insights.html">Insights</a></li>
        </ul>
      </div>
      <div>
        <h4>Get in touch</h4>
        <ul>
          <li><a href="mailto:{CFG['email']}">{CFG['email']}</a></li>
          <li><a href="tel:{CFG['phone_link']}">{CFG['phone_display']}</a></li>
          <li>{CFG['address']}</li>
          <li><a href="contact.html">Book a 20-minute call</a></li>
        </ul>
        <p class="fine" style="margin-top:14px">Desks operate 24 hours, Sunday evening to Saturday, with weekend cover by schedule.</p>
      </div>
    </div>
    <div class="foot-base">
      <span>&copy; <span id="year">2026</span> {CFG['company']}. Registered in Nepal. All rights reserved.</span>
      <span><a href="privacy.html">Privacy</a> &nbsp;&middot;&nbsp; <a href="terms.html">Terms</a> &nbsp;&middot;&nbsp; <a href="sitemap.xml">Sitemap</a></span>
    </div>
  </div>
</footer>
<button class="to-top" type="button" aria-label="Back to top">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
</button>
<script src="assets/js/main.js" defer></script>
</body>
</html>
"""


def page(path, title, desc, body, schema=None, extra=""):
    html = head(title, desc, path, schema, extra) + header(path) + body + footer()
    with open(os.path.join(SITE, path), "w", encoding="utf-8") as f:
        f.write(html)


def page_head(crumbs, h1, lead, seed=7):
    trail = "".join(f'<a href="{h}">{t}</a><span>/</span>' for h, t in crumbs)
    return f"""<section class="page-head on-dark">
  {hero_bg(seed)}
  <div class="wrap">
    <p class="crumb">{trail}</p>
    <h1 data-reveal="fade">{h1}</h1>
    <p class="lead" data-reveal="fade">{lead}</p>
  </div>
</section>
"""


def marquee(label="Systems our teams are trained on"):
    items = "".join(f'<span class="marquee-item">{s}</span>' for s in SYSTEMS)
    return f"""<section class="on-paper" style="padding-block:46px">
  <div class="wrap" style="text-align:center;margin-bottom:24px">
    <p class="small" style="margin:0 auto">{label}</p>
  </div>
  <div class="marquee"><div class="marquee-track">{items}</div></div>
  <div class="wrap" style="text-align:center;margin-top:24px">
    <p class="fine" style="margin:0 auto;max-width:62ch">We work inside your accounts on your licences. Product names are the property of their owners and their use here describes training, not endorsement or partnership.</p>
  </div>
</section>
"""


def faq_block(items, single=True):
    out = []
    for q, a in items:
        out.append(
            f'<details><summary>{q}<span class="plus" aria-hidden="true"></span></summary>'
            f'<div class="acc-body"><p>{a}</p></div></details>'
        )
    attr = ' data-acc-single' if single else ""
    return f'<div class="acc"{attr}>' + "".join(out) + "</div>"


# ============================================================== HOME =======
def build_home():
    svc_cards = "".join(f"""
      <article class="card svc" data-reveal>
        <span class="card-icon">{icon(s['icon'])}</span>
        <h3>{s['title']}</h3>
        <p>{s['blurb']}</p>
        <ul class="tags">{''.join(f'<li>{t}</li>' for t in s['tags'])}</ul>
        <a class="arrow-link" href="service-{s['slug']}.html">What this desk does {ARROW}</a>
      </article>""" for s in SERVICES)

    ind_cards = "".join(f"""
      <article class="card" data-reveal>
        <span class="card-icon">{icon(ic)}</span>
        <h3>{name}</h3>
        <p>{txt}</p>
      </article>""" for name, ic, txt, _tags in INDUSTRIES[:6])

    posts = "".join(f"""
      <article class="card" data-reveal>
        <p class="eyebrow">{p['cat']}</p>
        <h3>{p['title']}</h3>
        <p>{p['excerpt']}</p>
        <a class="arrow-link" href="insight-{p['slug']}.html">Read this {ARROW}</a>
      </article>""" for p in INSIGHTS)

    pillars = [
        ("users", "Supervised, never freelance",
         "Every account has a team lead on the floor and a separate QA reviewer who does not report to them. Nobody works your account alone and unchecked."),
        ("message", "Communication you can plan around",
         "A written report at the end of every shift, a weekly quality report with the raw sampling data, and a named person who answers in your working hours."),
        ("globe", "One centre, both halves of the clock",
         "Nepal sits at UTC+05:45. Our evening covers the American working day and our day covers the Gulf, Europe and Asia-Pacific, under one roof and one management line."),
        ("shield", "Evidence, not assurances",
         "Checks are logged with source and timestamp. Targets are published. QA data is shared raw, including the months it does not flatter us."),
    ]
    pillar_cards = "".join(f"""
      <article class="card pillar" data-reveal>
        <span class="card-icon">{icon(ic)}</span>
        <h3>{t}</h3>
        <p>{d}</p>
      </article>""" for ic, t, d in pillars)

    body = f"""
<section class="hero on-dark">
  {hero_bg(3)}
  <div class="wrap hero-grid">
    <div>
      <span class="hero-badge" data-enter><span class="pulse-dot"></span> Delivery centre in <b>Pokhara, Nepal</b></span>
      <h1 data-enter>Freight operations, handled while your team is
        <span class="rotator">
          <span>offline.</span><span>asleep.</span><span>at capacity.</span><span>on holiday.</span>
        </span>
      </h1>
      <p class="lead" data-enter>Supervised back-office teams for freight brokers, 3PLs, forwarders and customs brokers. Carrier vetting, track and trace, POD and billing, and forwarding documentation &mdash; worked inside your own systems, on your hours.</p>
      <div class="actions" data-enter>
        <a class="btn btn-amber" href="contact.html">Book a 20-minute call</a>
        <a class="btn btn-ghost" href="services.html">See all services</a>
      </div>
      <p class="hero-note" data-enter>From <b>$7 per hour</b> per dedicated team member &middot; Paid two-week pilot &middot; Month-to-month after that</p>
    </div>

    <div data-enter style="--d:240ms">
      <div class="board" aria-label="Illustration of a live overnight desk queue">
        <div class="board-head">
          <div class="clock here"><span>Pokhara desk</span><time data-tz="Asia/Kathmandu">--:--</time></div>
          <div class="clock"><span>Chicago</span><time data-tz="America/Chicago">--:--</time></div>
          <div class="clock"><span>Dubai</span><time data-tz="Asia/Dubai">--:--</time></div>
        </div>
        <table>
          <thead><tr><th scope="col">Item</th><th scope="col">Lane or party</th><th scope="col">Status</th></tr></thead>
          <tbody>
            <tr><td>Load 40821</td><td>Dallas, TX &rarr; Atlanta, GA</td><td><span class="status work" data-next="ETA confirmed, TMS updated">Check call due</span></td></tr>
            <tr><td>Carrier setup</td><td>New carrier, dry van</td><td><span class="status work" data-next="11 of 11 checks logged">Vetting in progress</span></td></tr>
            <tr><td>Load 40797</td><td>Fresno, CA &rarr; Denver, CO</td><td><span class="status work" data-next="POD received, invoiced">POD requested</span></td></tr>
            <tr><td>Carrier setup</td><td>Insurance mismatch</td><td><span class="status work" data-next="Flagged for your approval" data-next-class="flag">Verifying insurer</span></td></tr>
            <tr><td>HBL draft</td><td>Jebel Ali &rarr; Felixstowe</td><td><span class="status done">Sent for client sign-off</span></td></tr>
          </tbody>
        </table>
        <p class="board-foot">Illustration of a desk queue. Every approval decision stays with your team.</p>
      </div>
    </div>
  </div>
</section>

<section class="on-deep on-dark" style="padding-block:clamp(40px,4vw,64px)">
  <div class="wrap">
    <div class="stats">
      <div class="stat" data-reveal><span class="num">24/7</span><span class="lab">Hours covered, by design &mdash; the US night and the Gulf, European and Asian day from one centre</span></div>
      <div class="stat" data-reveal><span class="num">$<span data-count="7" data-decimals="0">0</span></span><span class="lab">Starting hourly rate per dedicated team member, published and not negotiated in the dark</span></div>
      <div class="stat" data-reveal><span class="num"><span data-count="98">0</span><i>%</i></span><span class="lab">Accuracy target from day one, rising to 99% from month three, measured by daily QA sampling</span></div>
      <div class="stat" data-reveal><span class="num"><span data-count="2">0</span></span><span class="lab">Weeks to prove it &mdash; a paid pilot on one process, with agreed targets and no long contract</span></div>
    </div>
    <p class="fine mt-m" style="max-width:74ch">These are the standards we commit to in writing, not past performance. Kamship Global launched its freight division in {CFG['founded']}; we will publish measured results, including the disappointing ones, as engagements mature.</p>
  </div>
</section>

{marquee()}

<section>
  <div class="wrap">
    <div class="split" style="margin-bottom:56px">
      <div data-reveal="left">
        <p class="eyebrow">Why teams choose us</p>
        <h2>Offshore work fails when nobody is watching it.</h2>
      </div>
      <div data-reveal="right">
        <p class="lead">One person, no supervision, no backup and no record is how most offshore freight support is sold, and it is why most of it disappoints. We built the opposite on purpose.</p>
      </div>
    </div>
    <div class="grid g-4">{pillar_cards}</div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:60ch;margin-bottom:12px" data-reveal>
      <p class="eyebrow">What we do</p>
      <h2>Eight desks, staffed by people who do nothing else</h2>
      <p class="lead">Pick one to start. Most clients begin with a single process and add desks once the first one is running without them thinking about it.</p>
    </div>
    <div class="grid g-3 mt-l">{svc_cards}</div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap">
    <div style="max-width:62ch" data-reveal>
      <p class="eyebrow">How the clock works</p>
      <h2>Two desks, one supervised centre</h2>
      <p class="lead">Nepal's time zone is the reason this works. The same building covers the American night and the Gulf, European and Asia-Pacific working day, without anyone working a shift they resent.</p>
    </div>

    <div class="tabs" data-tabs data-reveal>
      <div class="tablist" role="tablist" aria-label="Desk coverage">
        <button role="tab" id="tab-us" aria-controls="panel-us" aria-selected="true" tabindex="0">US brokerage night desk</button>
        <button role="tab" id="tab-fw" aria-controls="panel-fw" aria-selected="false" tabindex="-1">Global forwarding day desk</button>
      </div>

      <div role="tabpanel" id="panel-us" aria-labelledby="tab-us" class="tabpanel">
        <div class="split">
          <div>
            <h3>Your overnight back office</h3>
            <p>While your US office is closed, loads keep moving, carriers keep calling and paperwork keeps piling. This desk works that queue on your hours, inside your TMS, with your escalation rules.</p>
            <ul class="checks">
              <li>Carrier onboarding and documented vetting against your checklist</li>
              <li>Track and trace, check calls and TMS updates</li>
              <li>POD collection, billing packets and invoicing</li>
              <li>Carrier pay matching and accessorial checks</li>
              <li>After-hours carrier and driver messages, answered in your name</li>
            </ul>
            <div class="actions"><a class="btn btn-ghost btn-sm" href="industries.html">Who this suits</a></div>
          </div>
          <div class="table-wrap">
            <table class="data">
              <thead><tr><th scope="col">Your time zone</th><th scope="col">Our shift, Nepal time</th></tr></thead>
              <tbody>
                <tr><td>US Eastern</td><td>18:45 &ndash; 03:45</td></tr>
                <tr><td>US Central</td><td>19:45 &ndash; 04:45</td></tr>
                <tr><td>US Mountain</td><td>20:45 &ndash; 05:45</td></tr>
                <tr><td>US Pacific</td><td>21:45 &ndash; 06:45</td></tr>
                <tr><td>Weekend cover</td><td>By schedule, agreed in advance</td></tr>
              </tbody>
            </table>
            <p class="fine" style="padding:14px 20px 0">Shifts shown for US daylight time and adjusted twice a year with your calendar.</p>
          </div>
        </div>
      </div>

      <div role="tabpanel" id="panel-fw" aria-labelledby="tab-fw" class="tabpanel" hidden>
        <div class="split">
          <div>
            <h3>Documentation during your working day</h3>
            <p>For forwarders and customs brokers in the Gulf, the UK, Europe and Asia-Pacific, our normal daytime overlaps yours. Your desk is staffed while you are at your own.</p>
            <ul class="checks">
              <li>Shipment files in CargoWise, Magaya or your own system</li>
              <li>HBL and HAWB drafting, checked against the commercial documents</li>
              <li>Pre-alerts, arrival notices and milestone updates</li>
              <li>Customs entry data prepared for your licensed broker's review</li>
              <li>Container tracking, demurrage and detention alerts, job costing</li>
            </ul>
            <div class="actions"><a class="btn btn-ghost btn-sm" href="service-forwarding-documentation.html">Forwarding desk detail</a></div>
          </div>
          <div class="table-wrap">
            <table class="data">
              <thead><tr><th scope="col">Your region</th><th scope="col">Our shift, Nepal time</th></tr></thead>
              <tbody>
                <tr><td>Singapore, Hong Kong</td><td>06:00 &ndash; 15:00</td></tr>
                <tr><td>Australia, New Zealand</td><td>05:00 &ndash; 14:00</td></tr>
                <tr><td>UAE, Saudi Arabia, Qatar</td><td>11:00 &ndash; 20:00</td></tr>
                <tr><td>UK, Ireland, Europe</td><td>12:00 &ndash; 21:00</td></tr>
                <tr><td>Weekend cover</td><td>By schedule, agreed in advance</td></tr>
              </tbody>
            </table>
            <p class="fine" style="padding:14px 20px 0">Shift windows are indicative and set to your actual business hours during scoping.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Getting started</p>
      <h2>Three steps, no long contract</h2>
    </div>
    <ol class="steps">
      <li data-reveal><h3>Scope one process</h3><p>We complete an intake questionnaire with you, record a walkthrough, write the SOP together and agree accuracy, turnaround and volume targets in writing before anyone is hired to it.</p></li>
      <li data-reveal><h3>Run a paid two-week pilot</h3><p>You interview and approve the team. Access is named and multi-factor. Week one is fully quality-checked, with a report at the end of every shift and a review on day five.</p></li>
      <li data-reveal><h3>Scale month to month</h3><p>We review against the targets with the raw QA data on day ten. Hit them and we continue with 30 days' notice either way. Miss them and you walk away.</p></li>
    </ol>
    <div class="actions" data-reveal><a class="btn btn-primary" href="pilot.html">How the pilot works in detail</a></div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap split center">
    <div data-reveal="left">
      <p class="eyebrow">Carrier vetting</p>
      <h2>Carrier selection records now carry more weight.</h2>
      <p>In May 2026 the US Supreme Court held in <i>Montgomery v. Caribe Transport II</i> that state negligent-selection claims against freight brokers are not preempted by federal law. Many brokers now want consistent, retrievable evidence behind every carrier they use.</p>
      <p>Our vetting desk runs your written checklist on every new carrier and every pre-tender recheck, logs what was checked and when, and sends anything that fails to your approver. You set the policy and make the decision. We do the legwork and keep the file.</p>
      <div class="actions">
        <a class="btn btn-amber" href="service-carrier-vetting.html">How the vetting desk works</a>
        <a class="btn btn-ghost" href="insight-carrier-vetting-after-montgomery.html">Read our note on the ruling</a>
      </div>
    </div>
    <div data-reveal="right">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col">Commitment</th><th scope="col">Standard</th></tr></thead>
          <tbody>
            <tr><td>Checklist steps logged with source and time</td><td>100%</td></tr>
            <tr><td>Turnaround per new carrier file</td><td>Under 30 min</td></tr>
            <tr><td>Exceptions escalated, never overridden</td><td>100%</td></tr>
            <tr><td>Pre-tender rechecks before dispatch</td><td>100%</td></tr>
            <tr><td>Approval authority</td><td>Stays with you</td></tr>
          </tbody>
        </table>
      </div>
      <p class="fine mt-m">Operational service, not legal advice. It does not by itself make a broker compliant or protect against liability.</p>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Who we work with</p>
      <h2>Built for freight, not adapted to it</h2>
      <p class="lead">Our training, our SOP library and our QA sampling are all freight-specific. We do not take work outside it.</p>
    </div>
    <div class="grid g-3 mt-l">{ind_cards}</div>
    <div class="actions" data-reveal><a class="btn btn-ghost" href="industries.html">More on each sector</a></div>
  </div>
</section>

<section>
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Being straight with you</p>
      <h2>We have no client logos on this page yet.</h2>
      <p class="lead">Kamship Global's freight division launched in {CFG['founded']}. We are not going to borrow other companies' logos or publish testimonials we cannot evidence, because the whole offer here is that our records mean something.</p>
      <p>What we can give you instead is everything a careful buyer actually needs before committing: our written targets, our security controls, our pricing, an anonymised sample of the QA report you would receive weekly, and a paid pilot small enough that our track record does not have to be taken on trust.</p>
      <div class="actions">
        <a class="btn btn-primary" href="contact.html">Request a sample QA report</a>
        <a class="btn btn-ghost" href="security.html">Read our security controls</a>
      </div>
    </div>
    <div data-reveal="right">
      <div class="placeholder-note">
        <h3>What you get in week one of a pilot</h3>
        <ul class="checks amber">
          <li>The signed SOP we wrote together, with the escalation ladder</li>
          <li>A named team lead and a named QA reviewer, both of whom you met</li>
          <li>A report at the end of every shift, in writing</li>
          <li>100% quality checking, with the raw sample data attached</li>
          <li>A day-five review call while there is still time to change things</li>
          <li>A day-ten decision against the targets, with the evidence in front of you</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div class="split" style="margin-bottom:40px">
      <div data-reveal="left">
        <p class="eyebrow">Insights</p>
        <h2>Notes from the desk</h2>
      </div>
      <div data-reveal="right"><p class="lead">Practical writing about freight back-office work, from the people doing it.</p></div>
    </div>
    <div class="grid g-3">{posts}</div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Common questions</p>
      <h2>What buyers ask us first</h2>
    </div>
    <div class="mt-l" data-reveal>
      {faq_block([
        ("What does the pilot cost?", "Two weeks at the agreed hourly rate for each seat, invoiced before the start. Two operations-tier seats on the night desk come to roughly $1,500 for the fortnight. There is no separate setup or training fee: the three-week freight training our staff complete before they touch your account is ours to pay for, not yours."),
        ("Will your staff speak to our carriers and drivers?", "Most tracking now runs through apps, ELDs and email, and we use calls for exceptions. Staff on voice-facing desks pass a spoken-English assessment, and you interview them yourself before they start. If we think a voice-heavy role is a poor fit for our team, we will tell you rather than sell you the seats."),
        ("How do you access our systems?", "Through named accounts that you create, with multi-factor sign-in and only the permissions the work needs. Staff work from our supervised office on company-managed machines with USB storage, downloads and printing disabled. You can revoke access at any time, and we remove it within four hours of any staff change."),
        ("What happens if someone leaves or falls ill?", "A trained backup who already knows your process covers the shift. If a team member leaves, we replace them within 10 business days at no cost to you, and the backup carries the work in the meantime."),
        ("Do you work during Nepali holidays?", "Yes, by plan. We publish our holiday calendar before you sign and schedule cover in advance, with particular care around Dashain and Tihar in October and November. Being surprised by a festival is a planning failure, and we do not intend to have one."),
        ("Is there a minimum commitment?", "Two seats after the pilot, month to month with 30 days' notice on either side. No multi-year contract, no termination penalty and no seat you cannot give back."),
        ("Who owns the work and the data?", "You do. Work happens inside your systems, nothing is stored locally on our machines, and we sign your NDA plus a data processing agreement with UK or EU transfer clauses where your business needs them."),
      ])}
    </div>
  </div>
</section>

{cta()}
"""

    schema = {
        "@context": "https://schema.org",
        "@type": "ProfessionalService",
        "name": CFG["brand"],
        "legalName": CFG["company"],
        "url": CFG["domain"],
        "logo": CFG["domain"] + "/assets/img/kamship-icon-512.png",
        "image": CFG["domain"] + "/assets/img/og-image-1200x630.png",
        "email": CFG["email"],
        "telephone": CFG["phone_display"],
        "priceRange": "From $7 per hour",
        "description": "Supervised freight back-office teams for brokers, 3PLs, forwarders and customs brokers, delivered from Pokhara, Nepal.",
        "address": {"@type": "PostalAddress", "addressLocality": "Pokhara",
                    "addressRegion": "Gandaki", "addressCountry": "NP"},
        "areaServed": ["US", "CA", "GB", "IE", "AE", "SA", "QA", "SG", "HK", "AU", "NZ", "NL", "DE"],
        "sameAs": [CFG["linkedin"], CFG["facebook"]],
        "makesOffer": [{"@type": "Offer", "itemOffered": {"@type": "Service", "name": s["title"]}}
                       for s in SERVICES],
    }
    page("index.html",
         f"{CFG['brand']} | Freight back-office teams for brokers and forwarders",
         "Supervised freight back-office teams in Pokhara, Nepal: carrier vetting, tracking, POD and billing, forwarding documents. From $7/hour, paid two-week pilot.",
         body, schema)


# ========================================================== SERVICE PAGES ==
def build_services():
    cards = "".join(f"""
      <article class="card svc" data-reveal>
        <span class="card-icon">{icon(s['icon'])}</span>
        <h3>{s['title']}</h3>
        <p>{s['blurb']}</p>
        <ul class="tags">{''.join(f'<li>{t}</li>' for t in s['tags'])}</ul>
        <a class="arrow-link" href="service-{s['slug']}.html">What this desk does {ARROW}</a>
      </article>""" for s in SERVICES)

    body = page_head([("index.html", "Home")], "Eight freight desks you can staff",
                     "Each desk is a defined process with written targets, a trained team, an on-floor lead and daily quality checking. Start with one; add others when the first runs without you thinking about it.", 11)
    body += f"""
<section>
  <div class="wrap"><div class="grid g-3">{cards}</div></div>
</section>

{marquee()}

<section>
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">What every desk includes</p>
      <h2>The same structure behind all eight</h2>
      <p class="lead">The work differs. The way it is supervised, measured and reported does not.</p>
    </div>
    <div data-reveal="right">
      <ul class="checks">
        <li>An SOP written with you before anyone is assigned to the work</li>
        <li>A named team lead on the floor and a separate QA reviewer</li>
        <li>Written targets for accuracy, turnaround and volume</li>
        <li>A report at the end of every shift and a weekly quality report with raw data</li>
        <li>A trained backup who knows your process, for absence and holiday cover</li>
        <li>Replacement of any team member within 10 business days at no cost</li>
        <li>Named, multi-factor access to your systems, revocable in minutes</li>
      </ul>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Not sure where to start?</p>
      <h2>Pick the process that is easiest to judge</h2>
      <p class="lead">The first desk you hand over decides whether there is a second one. Choose for clarity rather than for volume.</p>
    </div>
    <div class="grid g-2 mt-l">
      <article class="card" data-reveal>
        <span class="card-icon">{icon('check')}</span>
        <h3>Good first candidates</h3>
        <ul class="checks">
          <li>POD collection and billing preparation</li>
          <li>Documented carrier vetting</li>
          <li>Appointment booking and carrier packet chasing</li>
          <li>Job file creation and arrival notices</li>
          <li>Load entry and data cleanup</li>
        </ul>
        <p class="small mt-m">High volume, obviously right or wrong, and a caught error costs an apology rather than an account.</p>
      </article>
      <article class="card" data-reveal>
        <span class="card-icon">{icon('search')}</span>
        <h3>Leave these until later</h3>
        <ul class="checks">
          <li>Rate negotiation and margin decisions</li>
          <li>Customer escalation handling</li>
          <li>Anything requiring a licence or professional sign-off</li>
          <li>Processes that live only in one person's memory</li>
        </ul>
        <p class="small mt-m">Not because they cannot be supported eventually, but because they are a poor test of whether the arrangement works.</p>
      </article>
    </div>
    <div class="actions" data-reveal><a class="btn btn-primary" href="insight-first-process-to-outsource.html">How to choose, in more detail</a></div>
  </div>
</section>

{cta()}
"""
    page("services.html", f"Freight outsourcing services | {CFG['brand']}",
         "Eight freight back-office desks: carrier vetting, track and trace, POD and billing, load entry, forwarding documentation, customs data and freight accounting.",
         body)


def build_service_pages():
    for i, s in enumerate(SERVICES):
        what = "".join(f"""
          <article class="card" data-reveal>
            <h3>{t}</h3>
            <p>{d}</p>
          </article>""" for t, d in s["what"])

        targets = "".join(f"<tr><td>{a}</td><td><b>{b}</b></td></tr>" for a, b in s["targets"])

        others = [o for o in SERVICES if o["slug"] != s["slug"]][:3]
        related = "".join(f"""
          <article class="card svc" data-reveal>
            <span class="card-icon">{icon(o['icon'])}</span>
            <h3>{o['title']}</h3>
            <p>{o['blurb']}</p>
            <a class="arrow-link" href="service-{o['slug']}.html">What this desk does {ARROW}</a>
          </article>""" for o in others)

        note = f'<p class="fine mt-m" style="max-width:72ch">{s["note"]}</p>' if s["note"] else ""

        body = page_head([("index.html", "Home"), ("services.html", "Services")],
                         s["title"], s["lead"], 20 + i)
        body += f"""
<section>
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">In short</p>
      <h2>{s['blurb']}</h2>
      <ul class="tags" style="margin-top:22px">{''.join(f'<li>{t}</li>' for t in s['tags'])}</ul>
      <div class="actions">
        <a class="btn btn-primary" href="contact.html">Discuss this desk</a>
        <a class="btn btn-ghost" href="pricing.html">See pricing</a>
      </div>
    </div>
    <div data-reveal="right">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col">What we commit to</th><th scope="col">Target</th></tr></thead>
          <tbody>{targets}</tbody>
        </table>
      </div>
      <p class="fine mt-m">Targets are agreed in writing during scoping and measured by daily QA sampling. The raw sampling data is shared with you weekly.</p>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">The work itself</p>
      <h2>What this desk handles</h2>
    </div>
    <div class="grid g-3 mt-l">{what}</div>
    {note}
  </div>
</section>

<section class="on-dark">
  <div class="wrap split center">
    <div data-reveal="left">
      <p class="eyebrow">Systems</p>
      <h2>We work in your tools, on your licences</h2>
      <p class="lead">Our teams are trained on {s['tools']}.</p>
      <p>We do not ask you to move to our platform, because there isn't one. Your data stays in your systems, your audit trail stays intact, and if you end the engagement your process carries on exactly where it was.</p>
    </div>
    <div data-reveal="right">
      <h3>What stays with you</h3>
      <ul class="checks">
        <li>Every approval, pricing and commercial decision</li>
        <li>Customer relationships and negotiations</li>
        <li>Licensed and professional judgements</li>
        <li>Your systems, your data and your audit trail</li>
      </ul>
      <p class="small mt-m">We execute defined steps and document them. Where a step needs judgement outside the SOP, it comes to your named approver rather than being decided on the floor.</p>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Also available</p>
      <h2>Other desks teams add</h2>
    </div>
    <div class="grid g-3 mt-l">{related}</div>
  </div>
</section>

{cta("Try this desk for two weeks.", "We will scope the process in your systems, agree the targets in writing, and run a paid two-week pilot. If the targets are missed, you walk away.")}
"""
        schema = {
            "@context": "https://schema.org",
            "@type": "Service",
            "serviceType": s["title"],
            "provider": {"@type": "Organization", "name": CFG["brand"], "url": CFG["domain"]},
            "areaServed": ["US", "CA", "GB", "AE", "SG", "AU"],
            "description": s["blurb"],
        }
        plain = s["blurb"].replace("&mdash;", "-").replace("&amp;", "and")
        page(f"service-{s['slug']}.html",
             f"{s['title']} | {CFG['brand']}", plain[:158], body, schema)


# =============================================================== COMPANY ===
def build_industries():
    cards = "".join(f"""
      <article class="card svc" data-reveal>
        <span class="card-icon">{icon(ic)}</span>
        <h3>{name}</h3>
        <p>{txt}</p>
        <ul class="tags">{''.join(f'<li>{t}</li>' for t in tags)}</ul>
      </article>""" for name, ic, txt, tags in INDUSTRIES)

    body = page_head([("index.html", "Home")], "Freight is the only sector we serve",
                     "Our training programme, SOP library and quality sampling are all built for freight. We turn down work outside it, because a team that does everything learns nothing deeply.", 31)
    body += f"""
<section><div class="wrap"><div class="grid g-3">{cards}</div></div></section>

<section class="on-paper">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Sizing</p>
      <h2>Built for companies of 20 to 300 staff</h2>
      <p class="lead">Big enough to have real volume in a defined process. Small enough that the large outsourcing firms will not take the account seriously.</p>
      <p>Large BPOs are built around hundred-seat engagements and price their governance accordingly. Freelance marketplaces go the other way and give you one unsupervised person. Freight companies of this size sit in between and are badly served by both.</p>
      <p>Our structure starts at two seats and still carries an on-floor lead, a separate QA reviewer, written targets and trained backup cover.</p>
    </div>
    <div data-reveal="right">
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th scope="col"></th><th scope="col">Freelancer</th><th scope="col">Kamship</th><th scope="col">Large BPO</th></tr></thead>
          <tbody>
            <tr><td>Minimum engagement</td><td>1 person</td><td><b>2 seats</b></td><td>20+ seats</td></tr>
            <tr><td>Supervision</td><td>None</td><td><b>Team lead + QA</b></td><td>Full</td></tr>
            <tr><td>Cover when absent</td><td>None</td><td><b>Trained backup</b></td><td>Pooled</td></tr>
            <tr><td>Written targets</td><td>Rare</td><td><b>Always</b></td><td>Always</td></tr>
            <tr><td>Freight specialism</td><td>Varies</td><td><b>Only freight</b></td><td>Varies</td></tr>
            <tr><td>Contract</td><td>Ad hoc</td><td><b>Month to month</b></td><td>1&ndash;3 years</td></tr>
          </tbody>
        </table>
      </div>
      <p class="fine mt-m">A generalised comparison of how these models are typically structured, offered to help you place us. Individual providers vary.</p>
    </div>
  </div>
</section>

{cta()}
"""
    page("industries.html", f"Industries we serve | {CFG['brand']}",
         "Freight brokers, 3PLs, forwarders, customs brokers, NVOCCs, shippers and freight audit firms. Freight is the only sector Kamship Global serves.",
         body)


def build_about():
    body = page_head([("index.html", "Home")], "A freight back office built in Pokhara",
                     f"{CFG['brand']} is the international operations division of {CFG['company']}, a Nepali company that also runs the KamShip AI recruitment platform.", 41)
    body += f"""
<section>
  <div class="wrap split">
    <div class="prose" data-reveal="left">
      <p class="eyebrow">Why we exist</p>
      <h2>Offshore freight support has a supervision problem</h2>
      <p>The usual offering is one person, hired through a marketplace, working alone from home with no team lead, no quality checking, no backup when they are ill and no record of what they did. It is cheap, and it mostly disappoints, and when it disappoints the conclusion drawn is that offshoring does not work for freight.</p>
      <p>We think the conclusion is wrong and the structure was the problem. So we built the structure first: a supervised floor, a team lead for every account, a QA reviewer who does not report to that lead, SOPs written with each client, trained backup for every seat, and a written report at the end of every shift.</p>
      <h2>Why freight, and only freight</h2>
      <p>Freight work is exacting, repetitive and time-critical, which is a hard combination for an in-house team under pressure and a good fit for a desk that does nothing else. It also happens at the wrong hours, and our time zone answers that better than almost anywhere.</p>
      <p>Staying in one sector means our three-week training programme can be specific, our SOP library compounds, and a team lead who has seen four hundred carrier files knows what a suspicious one looks like.</p>
      <h2>How we are set up</h2>
      <p>Our delivery centre is in Pokhara, Nepal's second city. We recruit from the city's universities and colleges and put every team member through a paid three-week Freight Academy before they touch a client account. Our sister platform, KamShip AI, gives us a large pool of pre-screened candidates across Nepal, which is why our hiring lead times are short.</p>
      <p>The centre runs two independent fibre providers on separate routes and backup power sized for the full floor, because a desk that goes dark at 3am is worse than no desk at all.</p>
      <h2>Honest by default</h2>
      <p>We publish our prices and our targets. We will not show client logos without written permission or publish testimonials we cannot evidence. If we think a piece of work is a poor fit for our team, we will say so rather than sell you the seats. If a month's quality data is bad, you will see the raw numbers, not a summary of them.</p>
      <p>That is not modesty. It is the only way a business whose product is documented, reliable process can credibly sell itself.</p>
    </div>
    <div data-reveal="right">
      <div class="card">
        <h3>At a glance</h3>
        <ul class="checks">
          <li><b>Division:</b> {CFG['brand']}, part of {CFG['company']}</li>
          <li><b>Delivery centre:</b> {CFG['city']}</li>
          <li><b>Sector:</b> Freight and logistics only</li>
          <li><b>Desks:</b> US brokerage night desk, global forwarding day desk</li>
          <li><b>Clients served from:</b> US, Canada, UK, Ireland, Europe, the Gulf, Singapore, Hong Kong, Australia and New Zealand</li>
          <li><b>Starting rate:</b> $7 per hour per dedicated team member</li>
          <li><b>Minimum:</b> Two seats after a paid two-week pilot</li>
          <li><b>Sister platform:</b> KamShip AI, our Nepal recruitment platform</li>
        </ul>
        <div class="actions"><a class="btn btn-primary btn-sm" href="contact.html">Talk to us</a></div>
      </div>
    </div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">How we work</p>
      <h2>Four commitments we will not trade away</h2>
    </div>
    <div class="grid g-4 mt-l">
      <article class="card pillar" data-reveal><span class="card-icon">{icon('users')}</span><h3>Nobody works alone</h3><p>Every account has a team lead on the floor and a separate QA reviewer. No client is ever served by one unsupervised person.</p></article>
      <article class="card pillar" data-reveal><span class="card-icon">{icon('shield')}</span><h3>The record is the product</h3><p>What was checked, when, by whom and with what evidence. If we cannot show it, we do not claim it.</p></article>
      <article class="card pillar" data-reveal><span class="card-icon">{icon('lock')}</span><h3>Decisions stay yours</h3><p>We execute defined steps. Approvals, pricing, licensed judgements and customer relationships remain with your team.</p></article>
      <article class="card pillar" data-reveal><span class="card-icon">{icon('spark')}</span><h3>People are trained, not rented</h3><p>Three paid weeks of freight training before any client work, above-market pay, and a route to Specialist, Team Lead and QA roles.</p></article>
    </div>
  </div>
</section>

{cta()}
"""
    page("about.html", f"About {CFG['brand']}",
         "The international freight operations division of Kamship Pvt Ltd, with a supervised delivery centre in Pokhara, Nepal.",
         body)


def build_why_nepal():
    body = page_head([("index.html", "Home"), ("about.html", "Company")],
                     "Why Nepal, and why Pokhara",
                     "A time zone that covers both halves of the working world, a talent pool nobody else is fighting over, and costs that let us charge from $7 an hour without cutting corners on supervision.", 47)
    body += f"""
<section>
  <div class="wrap split">
    <div class="prose" data-reveal="left">
      <h2>The time zone</h2>
      <p>Nepal runs at UTC+05:45, which is an unusual offset and an unusually useful one. Our evening and night cover the American working day. Our daytime covers the Gulf, most of Europe and much of Asia-Pacific. One centre, one building, one management line, both halves of the clock.</p>
      <p>For a US broker this means the overnight queue is worked by people on their normal schedule with a supervisor present, rather than by someone at home at 4am fighting to stay awake.</p>
      <h2>The talent pool</h2>
      <p>Roughly nine in ten of Nepal's IT and outsourcing jobs are in Kathmandu. Pokhara has the universities and the graduates but far less competition for them, which shows up in the number that matters most in this business: attrition.</p>
      <p>A seat that turns over every five months is not cheap however low the rate. You pay for it in retraining, in errors, and in the quiet erosion of everything you carefully documented. Lower churn is the real product of choosing a second city.</p>
      <h2>The cost structure</h2>
      <p>Nepali salaries let us pay above the local market, fund three weeks of training before anyone bills, staff a team lead and a QA reviewer on every account, and still start at $7 an hour. In Manila or Bengaluru the same supervision ratio costs materially more.</p>
      <h2>The infrastructure question</h2>
      <p>The fair question is whether Nepal can keep the lights on and the internet up. A centre has to plan for both, and ours does: two independent fibre providers on separate physical routes, and backup power sized for the full floor rather than for a few desks. Any provider unwilling to discuss this in specifics is one to avoid.</p>
      <h2>The festival calendar</h2>
      <p>Dashain and Tihar fall in October and November and matter enormously here. We publish the holiday calendar before you sign and schedule cover in advance. Being surprised by Dashain is a planning failure, not a cultural one.</p>
      <h2>Where Nepal is not the answer</h2>
      <p>Licensed work stays with your licensed people: we prepare customs entries, we do not file them. Heavy outbound sales calling into some markets is a poor fit and we will say so. Below about two full-time seats the supervision structure costs more than it returns for you. And a process that exists only in one person's head will fail offshore for reasons that have nothing to do with the team receiving it.</p>
    </div>
    <div data-reveal="right">
      <div class="card">
        <h3>Coverage from one centre</h3>
        <div class="table-wrap" style="border:0">
          <table class="data" style="min-width:0">
            <tbody>
              <tr><td>United States</td><td>Full business day, our night</td></tr>
              <tr><td>Canada</td><td>Full business day, our night</td></tr>
              <tr><td>UK and Ireland</td><td>Morning and afternoon</td></tr>
              <tr><td>Europe</td><td>Morning and afternoon</td></tr>
              <tr><td>UAE and the Gulf</td><td>Full business day</td></tr>
              <tr><td>Singapore, Hong Kong</td><td>Full business day, our early shift</td></tr>
              <tr><td>Australia, New Zealand</td><td>Late morning and afternoon</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card mt-m">
        <h3>What we commit to on continuity</h3>
        <ul class="checks">
          <li>Two independent fibre providers on separate routes</li>
          <li>Backup power sized for the full floor</li>
          <li>Trained backup staff for every seat</li>
          <li>Holiday calendar published before you sign</li>
          <li>97% seat availability including cover</li>
        </ul>
      </div>
    </div>
  </div>
</section>

{cta()}
"""
    page("why-nepal.html", f"Why Nepal for freight outsourcing | {CFG['brand']}",
         "Nepal's UTC+05:45 time zone covers the US night and the Gulf and European day from one centre. Why Pokhara means lower attrition, and where Nepal is not the answer.",
         body)


def build_security():
    groups = [
        ("Physical", "pin", [
            "Client work happens only in our supervised Pokhara office, never from home",
            "Controlled entry, visitor log and CCTV covering the work floor",
            "Clean-desk policy, with personal phones and bags in lockers during shifts",
            "Separate, access-controlled zones where a client requires segregation",
        ]),
        ("Devices and network", "lock", [
            "Company-owned, centrally managed computers with full-disk encryption",
            "USB storage, local downloads and printing disabled by policy and by configuration",
            "Endpoint protection with centrally managed patching",
            "Business firewall; personal webmail and file-sharing services blocked",
            "No client data stored on local machines &mdash; work happens inside your systems",
        ]),
        ("Access control", "shield", [
            "Named accounts only. We never share logins between staff",
            "Multi-factor sign-in on every client system",
            "Least-privilege permissions, agreed per engagement and reviewed quarterly",
            "All access removed within 4 hours of any staff change",
            "Client credentials held in a business password manager, never in documents or chat",
        ]),
        ("People", "users", [
            "Background checks including a police clearance report before hire",
            "Written confidentiality obligations in every employment contract",
            "Security and privacy training before system access, refreshed annually",
            "Named team lead and QA reviewer per account, both known to you",
        ]),
        ("Incidents and continuity", "cycle", [
            "Documented incident response procedure with defined roles",
            "Clients notified within 24 hours of a suspected breach affecting their data",
            "Backup power and two independent internet providers",
            "Trained backup staff so a single absence never stops the desk",
        ]),
        ("Agreements", "file", [
            "We sign your NDA before scoping begins",
            "Data processing agreement with UK IDTA or EU Standard Contractual Clauses where needed",
            "Defined data retention and a documented offboarding checklist",
            "Your right to audit our controls, written into the agreement",
        ]),
    ]
    cards = "".join(f"""
      <article class="card" data-reveal>
        <span class="card-icon">{icon(ic)}</span>
        <h3>{t}</h3>
        <ul class="checks">{''.join(f'<li>{x}</li>' for x in xs)}</ul>
      </article>""" for t, ic, xs in groups)

    body = page_head([("index.html", "Home"), ("about.html", "Company")],
                     "Security and data handling",
                     "A summary of the controls we operate. We complete your security questionnaire and share the full overview on request, before you send us anything.", 53)
    body += f"""
<section><div class="wrap"><div class="grid g-3">{cards}</div></div></section>

<section class="on-dark">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Certification status</p>
      <h2>What we hold, and what we do not</h2>
      <p>We are working toward alignment with ISO/IEC 27001 and will say exactly that until we are certified. We are not certified today, and we will not imply otherwise on a page like this.</p>
      <p>What we can do now is complete your security questionnaire in full, walk your team through our controls on a call, give you the right to audit them in the agreement, and start with a pilot small enough that the risk is proportionate to what you know about us.</p>
      <div class="actions"><a class="btn btn-amber" href="contact.html">Request the security overview</a></div>
    </div>
    <div data-reveal="right">
      <h3>Questions we are asked, answered plainly</h3>
      {faq_block([
        ("Where is our data stored?", "In your systems. Our staff work inside your TMS, portals and document stores through named accounts. We do not copy client data onto our machines, and local storage and downloads are disabled by configuration, not just by policy."),
        ("Can we audit you?", "Yes. The right to audit our controls is written into our standard agreement, and we will host an inspection of the floor with reasonable notice."),
        ("What about UK and EU personal data?", "We sign a data processing agreement incorporating the UK International Data Transfer Addendum or EU Standard Contractual Clauses before any personal data is shared, and we document the categories of data involved."),
        ("What happens when we end the engagement?", "A documented offboarding checklist: access revoked within four hours, any client materials returned or destroyed to your instruction, and written confirmation from us that it has been done."),
        ("Who is liable if something goes wrong?", "Liability is set out in the signed agreement, which your lawyer should review. We carry the obligations we agree to and we will not hide them in a website footnote."),
      ], single=False)}
    </div>
  </div>
</section>

{cta("Send us your security questionnaire.", "We complete it in full before you commit to anything, and we will walk your IT or compliance team through the answers on a call.")}
"""
    page("security.html", f"Security and data handling | {CFG['brand']}",
         "Physical, device, access, people and incident controls at Kamship Global's Pokhara delivery centre, plus our certification status stated plainly.",
         body)


def build_pilot():
    body = page_head([("index.html", "Home"), ("about.html", "Company")],
                     "Two weeks, one process, agreed targets",
                     "A paid pilot lets you judge the work in your own systems, with your own eyes, before committing to anything longer. It is deliberately small and completely reversible.", 59)
    body += f"""
<section>
  <div class="wrap">
    <ol class="steps">
      <li data-reveal><h3>Scope &mdash; the week before</h3><p>We complete an intake questionnaire with you, record a walkthrough of the process, write the SOP together and agree accuracy, turnaround and volume targets in writing. Nothing is hired to the work until this is signed off.</p></li>
      <li data-reveal><h3>Pilot &mdash; two weeks</h3><p>You interview and approve the team. Named access with multi-factor sign-in is set up. Week one is 100% quality-checked, with a written report at the end of every shift and a review call on day five while there is still time to change course.</p></li>
      <li data-reveal><h3>Decide &mdash; day ten</h3><p>We review the results against the targets with the raw QA data in front of you. Hit them and we continue month to month. Miss them and you walk away, owing nothing beyond the pilot fee.</p></li>
    </ol>
  </div>
</section>

<section class="on-paper">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Timeline</p>
      <h2>What happens on which day</h2>
      <p class="lead">Nothing here is a surprise. You will know before the pilot starts exactly what lands in your inbox and when.</p>
    </div>
    <div class="table-wrap" data-reveal="right">
      <table class="data">
        <thead><tr><th scope="col">When</th><th scope="col">What happens</th></tr></thead>
        <tbody>
          <tr><td><b>Day &minus;10</b></td><td>Intake questionnaire and process walkthrough recorded</td></tr>
          <tr><td><b>Day &minus;7</b></td><td>SOP drafted by us, reviewed and corrected by you</td></tr>
          <tr><td><b>Day &minus;5</b></td><td>Targets agreed in writing; NDA and pilot agreement signed</td></tr>
          <tr><td><b>Day &minus;4</b></td><td>You interview and approve the assigned team</td></tr>
          <tr><td><b>Day &minus;2</b></td><td>Named system access created and tested; training on your SOP</td></tr>
          <tr><td><b>Day 1</b></td><td>Desk goes live; first end-of-shift report that evening</td></tr>
          <tr><td><b>Days 1&ndash;5</b></td><td>100% of output quality-checked; daily reports</td></tr>
          <tr><td><b>Day 5</b></td><td>Review call: what is working, what needs correcting</td></tr>
          <tr><td><b>Days 6&ndash;10</b></td><td>QA sampling moves to agreed rate; weekly quality report</td></tr>
          <tr><td><b>Day 10</b></td><td>Decision review against the written targets, with raw data</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Fair questions</p>
      <h2>Before you commit to a fortnight</h2>
    </div>
    <div class="mt-l" data-reveal>
      {faq_block([
        ("What does the pilot cost?", "Two weeks at the agreed hourly rate for each seat, invoiced before the start. Two operations-tier seats on the night desk come to roughly $1,500. There is no setup fee and no training charge &mdash; the three-week Freight Academy our staff complete is our cost, not yours."),
        ("Why is the pilot paid?", "Because a free trial is staffed by whoever is spare, and you learn nothing from it. A paid pilot gets a properly assigned, properly trained team and a real SOP, which is the only version worth judging."),
        ("What if we want to stop after week one?", "Tell us and we stop. You have paid for the fortnight, the access gets revoked the same day, and there is nothing further to unwind."),
        ("What happens if you miss the targets?", "You walk away and owe nothing beyond the pilot fee. If we think the miss was caused by something fixable, we will say so and you can decide; we will not argue that a missed target was actually met."),
        ("Can we pilot two processes at once?", "We would rather you did not. One process with real attention teaches you more about whether this arrangement works than two processes half-supervised."),
        ("What do we need to provide?", "A named person who can answer questions during our shift overlap, system access, and about three hours of someone's time for the walkthrough and SOP review. That is genuinely the whole list."),
        ("What happens after a successful pilot?", "Month-to-month at the same rate, with 30 days' notice on either side. Add seats or desks when you want them; give them back when you do not."),
      ])}
    </div>
  </div>
</section>

{cta("Ready to scope one process?", "Twenty minutes on a call tells us whether there is a sensible pilot here. If there is not, we will tell you that too.")}
"""
    page("pilot.html", f"How the two-week pilot works | {CFG['brand']}",
         "Scope one process, run a paid two-week pilot with written targets, then continue month to month. Full day-by-day timeline and answers to the questions buyers ask.",
         body)


# =============================================================== PRICING ===
def build_pricing():
    tiers = [
        ("Data &amp; documents", "7", "7.70", False,
         "Load entry, POD and BOL indexing, document collection, data cleanup and list work.",
         ["Order and load entry", "POD and document indexing", "Data cleanup and enrichment",
          "Carrier packet chasing", "Daily shift report", "Team lead and QA included"],
         "$1,211 &ndash; $1,332"),
        ("Operations desk", "8.50", "9.35", True,
         "Track and trace, carrier vetting, appointment booking, forwarding job files and arrival notices.",
         ["Everything in Data &amp; documents", "Track and trace with check calls",
          "Documented carrier vetting", "Appointment booking and portal work",
          "CargoWise or Magaya job files", "Exception escalation to your on-call"],
         "$1,471 &ndash; $1,618"),
        ("Specialist desk", "10.50", "11.55", False,
         "Freight billing and AR/AP, customs data preparation, experienced CargoWise operators and after-hours desk leads.",
         ["Everything in Operations desk", "Freight accounting, AR and AP",
          "Customs entry data preparation", "Senior CargoWise or Magaya operator",
          "After-hours desk lead role", "Month-end and reconciliation support"],
         "$1,817 &ndash; $1,998"),
    ]
    cards = ""
    for name, day, night, feat, blurb, feats, monthly in tiers:
        flag = '<span class="flag">Most chosen</span>' if feat else ""
        cards += f"""
        <article class="price{' featured' if feat else ''}" data-reveal>
          {flag}
          <h3>{name}</h3>
          <p class="small">{blurb}</p>
          <p class="amount" style="margin-top:18px"><sup>$</sup>{day}<span style="font-size:1rem;font-weight:600;letter-spacing:0"> /hour</span></p>
          <p class="per">${night}/hour on the US night desk &middot; {monthly} per seat per month</p>
          <ul class="checks">{''.join(f'<li>{x}</li>' for x in feats)}</ul>
          <a class="btn {'btn-primary' if feat else 'btn-ghost'}" href="contact.html">Get a quote</a>
        </article>"""

    body = page_head([("index.html", "Home")], "Straightforward hourly pricing",
                     "Pay per dedicated team member, monthly in advance. Supervision, quality checking, backup cover, equipment, office and training are all included, not billed separately.", 61)
    body += f"""
<section>
  <div class="wrap">
    <div class="price-grid">{cards}</div>
    <p class="fine mt-m" style="max-width:76ch">Monthly figures assume 173 hours. Night rates apply to shifts covering the US business day and carry a 10% premium, which funds the night allowance and safe transport we provide our staff. Minimum two seats after the pilot. Volume pricing from five seats. Weekend and holiday cover scheduled in advance. Prices in US dollars, invoiced monthly in advance, payable by bank transfer.</p>
  </div>
</section>

<section class="on-paper">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">Included as standard</p>
      <h2>No setup fee, no training charge, no governance line item</h2>
      <p class="lead">The things other providers charge for separately are inside the hourly rate, because they are not optional extras &mdash; they are what makes the work reliable.</p>
      <ul class="checks">
        <li>Recruitment, assessment and a paid three-week Freight Academy before your work starts</li>
        <li>An on-floor team lead and a separate QA reviewer for your account</li>
        <li>Daily shift reports and a weekly quality report with the raw sampling data</li>
        <li>A trained backup who knows your process, for absence and holiday cover</li>
        <li>Replacement of any team member within 10 business days at no cost</li>
        <li>Office, company-managed equipment, backup power and two internet providers</li>
        <li>Account management and a named person who answers in your hours</li>
      </ul>
    </div>
    <div data-reveal="right">
      <div class="card">
        <h3>What a US hire actually costs</h3>
        <div class="table-wrap" style="border:0">
          <table class="data" style="min-width:0">
            <tbody>
              <tr><td>Base pay at $20/hour</td><td>$20.00</td></tr>
              <tr><td>Payroll taxes and benefits (about 30%)</td><td>$6.00</td></tr>
              <tr><td><b>Loaded hourly cost</b></td><td><b>$26.00</b></td></tr>
              <tr><td>Kamship operations desk, night rate</td><td>$9.35</td></tr>
              <tr><td><b>Difference</b></td><td><b>About 64%</b></td></tr>
            </tbody>
          </table>
        </div>
        <p class="fine mt-m">An illustration using a typical loaded-cost assumption, not a quote or a promise about your business. Recruitment cost, turnover and management time are excluded from both sides, and they tend to favour the outsourced side further.</p>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Commercials</p>
      <h2>Pricing questions, answered</h2>
    </div>
    <div class="mt-l" data-reveal>
      {faq_block([
        ("Why are rates published at all?", "Because hiding them wastes your time and ours. Freight companies comparing options need a number to compare. If our number does not work for you, better that you know in thirty seconds than after three calls."),
        ("What counts as a night rate?", "Any shift covering the US business day, which is our night. The 10% premium funds the night allowance, meals and the safe door-to-door transport we provide staff on those shifts, which is both the right thing to do and a legal requirement here for women working night hours."),
        ("Is there a discount for more seats?", "Yes, from five seats. Tell us the likely shape of the engagement and we will quote it properly rather than applying a blanket percentage."),
        ("How do we pay from outside Nepal?", "Bank transfer by SWIFT to our Nepali bank account, invoiced monthly in advance in US dollars. We will give you the full remittance details and the charges to expect during onboarding."),
        ("Are there any other costs?", "Only ones we agree with you in writing beforehand, and there usually are none. If your work needs a paid licence, subscription or phone number in your name, that sits with you and we will tell you before it is needed."),
        ("What if we need to scale down?", "Thirty days' notice, no penalty. We would rather you scaled down and stayed than felt trapped and left."),
      ])}
    </div>
  </div>
</section>

{cta("Want this priced for your actual volumes?", "Send us the process and the weekly volume and we will come back with a seat count and a monthly number, not a range.")}
"""
    page("pricing.html", f"Pricing | {CFG['brand']}",
         "From $7 per hour per dedicated freight team member. Three tiers, published rates, no setup fee. Supervision, QA, backup cover and equipment included.",
         body)


# =============================================================== CAREERS ===
def build_careers():
    roles = [
        ("Freight Operations Associate", "Night and day shifts",
         "Track shipments, check carriers, prepare documents and keep clients in the US, UK, the Gulf and Asia-Pacific updated, working inside their systems with a team lead beside you.",
         ["Good written English", "Careful attention to detail", "Comfortable with computers and spreadsheets", "Graduates and final-year students welcome"]),
        ("Freight Documentation Specialist", "Day shift",
         "Prepare shipment files, draft house bills and arrival notices, and keep job costing accurate for forwarders in the Gulf, Europe and Asia-Pacific.",
         ["Strong written English", "Accuracy under deadline", "Interest in international trade", "Prior documentation experience helps but is not required"]),
        ("Team Lead, Operations", "Night shift",
         "Run a client desk on the floor: assign work, coach associates, handle escalations and own the shift report.",
         ["2+ years in an operations or BPO role", "Experience supervising a small team", "Confident spoken and written English", "Available for night shifts"]),
        ("Quality Assurance Reviewer", "Day and night shifts",
         "Sample completed work against the SOP, record findings honestly, and produce the weekly quality report clients read.",
         ["Exceptional attention to detail", "Comfortable giving difficult feedback", "Strong written English", "Independent judgement"]),
    ]
    cards = "".join(f"""
      <article class="card svc" data-reveal>
        <p class="eyebrow">{shift}</p>
        <h3>{title}</h3>
        <p>{desc}</p>
        <ul class="checks">{''.join(f'<li>{r}</li>' for r in reqs)}</ul>
        <a class="arrow-link" href="{CFG['jobs']}">Apply on KamShip AI {ARROW}</a>
      </article>""" for title, shift, desc, reqs in roles)

    body = page_head([("index.html", "Home")], "Build a freight career in Pokhara",
                     "Learn how global freight actually works, serve clients on four continents, and build a career with international standards &mdash; without leaving Pokhara.", 67)
    body += f"""
<section>
  <div class="wrap">
    <div class="stats" style="margin-bottom:60px">
      <div class="stat" data-reveal><span class="num"><span data-count="3">0</span></span><span class="lab">Paid weeks of Freight Academy training before your first client shift</span></div>
      <div class="stat" data-reveal><span class="num"><span data-count="4">0</span></span><span class="lab">Continents your clients are on &mdash; the US, Europe, the Gulf and Asia-Pacific</span></div>
      <div class="stat" data-reveal><span class="num"><span data-count="100">0</span><i>%</i></span><span class="lab">Safe door-to-door transport provided on night and early shifts</span></div>
      <div class="stat" data-reveal><span class="num"><span data-count="3">0</span></span><span class="lab">Promotion routes from Associate: Specialist, Team Lead and QA Reviewer</span></div>
    </div>
    <div class="grid g-2">{cards}</div>
  </div>
</section>

<section class="on-dark">
  <div class="wrap split">
    <div data-reveal="left">
      <p class="eyebrow">What we offer</p>
      <h2>The things that actually matter in a night-shift job</h2>
      <ul class="checks">
        <li>A paid three-week Freight Academy before you touch a client account</li>
        <li>Pay above the local market, plus a night-shift allowance</li>
        <li>Safe door-to-door transport and a meal on night and early shifts</li>
        <li>Social Security Fund enrolment and festival allowance from day one</li>
        <li>A written career path to Specialist, Team Lead and QA Reviewer</li>
        <li>A team lead beside you on the floor, not a manager in another country</li>
        <li>Real international experience that travels with you</li>
      </ul>
    </div>
    <div data-reveal="right">
      <div class="card">
        <h3>How to apply</h3>
        <p>All our roles are listed on KamShip AI, our own recruitment platform. Create a profile, upload your CV and apply in a few minutes. We reply to every application.</p>
        <p class="small">Applications from women are particularly welcome, including for night-shift roles, where we provide safe transport and comply fully with the Labour Act 2074 requirements.</p>
        <div class="actions">
          <a class="btn btn-amber" href="{CFG['jobs']}">See open roles</a>
          <a class="btn btn-ghost" href="mailto:{CFG['email']}?subject=Career%20enquiry">Email us</a>
        </div>
      </div>
      <div class="card mt-m">
        <h3>Hiring process</h3>
        <ol class="steps" style="grid-template-columns:1fr;gap:18px;margin-top:20px">
          <li style="padding-top:0;padding-left:64px;min-height:44px"><h4 style="margin-bottom:4px">Apply on KamShip AI</h4><p class="small">CV and short profile</p></li>
          <li style="padding-top:0;padding-left:64px;min-height:44px"><h4 style="margin-bottom:4px">Written and computer assessment</h4><p class="small">English, accuracy and basic spreadsheet skills</p></li>
          <li style="padding-top:0;padding-left:64px;min-height:44px"><h4 style="margin-bottom:4px">Interview in Pokhara</h4><p class="small">With the team lead you would work for</p></li>
          <li style="padding-top:0;padding-left:64px;min-height:44px"><h4 style="margin-bottom:4px">Offer and Freight Academy</h4><p class="small">Three paid weeks before your first shift</p></li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Questions candidates ask</p>
      <h2>Before you apply</h2>
    </div>
    <div class="mt-l" data-reveal>
      {faq_block([
        ("Do I need freight experience?", "No. The three-week Freight Academy exists precisely because almost nobody in Nepal arrives with freight experience. We hire for careful attention to detail and good written English, and we teach the freight."),
        ("What are the shift timings?", "Night desk shifts run roughly 18:45 to 03:45 through to 21:45 to 06:45 Nepal time depending on which US time zone the client is in. Day desk shifts run from about 05:00 to 14:00 or 12:00 to 21:00 for Asia-Pacific, the Gulf and Europe. You apply for a specific shift and we do not move you without agreement."),
        ("Is transport really provided?", "Yes, door to door, for every night and early shift, for everyone. It is a legal requirement for women working night hours under the Labour Act 2074 and we extend it to all staff because it is the right thing to do."),
        ("Can I move to a day shift later?", "Usually yes, as day desks grow. Say so at interview and we will keep it in mind rather than surprising you."),
        ("Is this a call-centre job?", "Not mainly. Most of the work is in systems: entering, checking, documenting and updating. Some roles include calls to carriers and drivers, and those roles are clearly marked and include a spoken-English assessment."),
        ("Where is the office?", "Pokhara. We will confirm the exact location with candidates during the hiring process."),
      ])}
    </div>
  </div>
</section>

<section class="cta-band on-dark">
  {hero_bg(77)}
  <div class="wrap" style="text-align:center">
    <h2 style="margin-inline:auto" data-reveal>Your freight career starts with one application.</h2>
    <p class="lead" style="margin-inline:auto" data-reveal>Paid training, international clients, and a real path upward &mdash; from Pokhara.</p>
    <div class="actions" style="justify-content:center" data-reveal><a class="btn btn-amber" href="{CFG['jobs']}">See open roles on KamShip AI</a></div>
  </div>
</section>
"""
    page("careers.html", f"Careers in Pokhara | {CFG['brand']}",
         "Freight Operations Associate, Documentation Specialist, Team Lead and QA roles in Pokhara. Paid three-week training, above-market pay and safe transport on night shifts.",
         body)


# ============================================================== INSIGHTS ===
def build_insights():
    cards = "".join(f"""
      <article class="card svc" data-reveal>
        <p class="eyebrow">{p['cat']}</p>
        <h3>{p['title']}</h3>
        <p class="small"><time datetime="{p['date']}">{p['date_display']}</time></p>
        <p>{p['excerpt']}</p>
        <a class="arrow-link" href="insight-{p['slug']}.html">Read this {ARROW}</a>
      </article>""" for p in INSIGHTS)

    body = page_head([("index.html", "Home")], "Notes from the desk",
                     "Practical writing about freight back-office work: what it takes to run it well, where outsourcing helps, and where it does not.", 71)
    body += f"""
<section><div class="wrap"><div class="grid g-3">{cards}</div></div></section>
{cta()}
"""
    page("insights.html", f"Insights | {CFG['brand']}",
         "Practical writing on freight back-office operations, carrier vetting, offshore delivery and choosing the first process to outsource.",
         body)


def build_insight_posts():
    for i, p in enumerate(INSIGHTS):
        blocks = ""
        for kind, val in p["body"]:
            if kind == "h2":
                blocks += f"<h2>{val}</h2>"
            elif kind == "p":
                blocks += f"<p>{val}</p>"
            elif kind == "blockquote":
                blocks += f"<blockquote>{val}</blockquote>"
            elif kind == "ul":
                blocks += "<ul>" + "".join(f"<li>{x}</li>" for x in val) + "</ul>"

        others = [o for o in INSIGHTS if o["slug"] != p["slug"]]
        more = "".join(f"""
          <article class="card svc" data-reveal>
            <p class="eyebrow">{o['cat']}</p>
            <h3>{o['title']}</h3>
            <p>{o['excerpt']}</p>
            <a class="arrow-link" href="insight-{o['slug']}.html">Read this {ARROW}</a>
          </article>""" for o in others)

        body = page_head([("index.html", "Home"), ("insights.html", "Insights")],
                         p["title"],
                         f'<time datetime="{p["date"]}">{p["date_display"]}</time> &middot; {p["cat"]}', 80 + i)
        body += f"""
<section>
  <div class="wrap-narrow">
    <article class="prose" data-reveal="fade">{blocks}</article>
    <div class="card mt-l" data-reveal>
      <h3>Want to talk about this in your own operation?</h3>
      <p>Twenty minutes on a call, no deck. Tell us where the process is stretched and we will tell you honestly whether a desk would help.</p>
      <div class="actions"><a class="btn btn-primary" href="contact.html">Book a call</a></div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal><p class="eyebrow">Keep reading</p><h2>More from the desk</h2></div>
    <div class="grid g-2 mt-l">{more}</div>
  </div>
</section>
"""
        schema = {
            "@context": "https://schema.org", "@type": "Article",
            "headline": p["title"], "datePublished": p["date"],
            "author": {"@type": "Organization", "name": CFG["brand"]},
            "publisher": {"@type": "Organization", "name": CFG["brand"],
                          "logo": {"@type": "ImageObject", "url": CFG["domain"] + "/assets/img/kamship-icon-512.png"}},
            "description": p["excerpt"],
        }
        page(f"insight-{p['slug']}.html", f"{p['title']} | {CFG['brand']}",
             p["excerpt"][:158], body, schema)


# =============================================================== CONTACT ===
def build_contact():
    svc_opts = "".join(f'<option>{s["title"]}</option>' for s in SERVICES)
    body = page_head([("index.html", "Home")], "Tell us which process is stretched",
                     "Twenty minutes on a call is usually enough to tell whether there is a sensible pilot here. If there is not, we will say so.", 89)
    body += f"""
<section>
  <div class="wrap split">
    <div data-reveal="left">
      <form class="form-card" id="enquiry-form" novalidate>
        <h2 style="font-size:var(--t-2)">Send us an enquiry</h2>
        <p class="small" style="margin-bottom:26px">Everything except your name and email is optional, but the more you tell us the more useful our first reply will be.</p>

        <div class="field-row">
          <div class="field">
            <label for="f-name">Your name</label>
            <input id="f-name" name="name" type="text" autocomplete="name" required>
          </div>
          <div class="field">
            <label for="f-company">Company</label>
            <input id="f-company" name="company" type="text" autocomplete="organization">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="f-email">Work email</label>
            <input id="f-email" name="email" type="email" autocomplete="email" required>
          </div>
          <div class="field">
            <label for="f-phone">Phone or WhatsApp <span class="hint">optional</span></label>
            <input id="f-phone" name="phone" type="tel" autocomplete="tel">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="f-country">Country and time zone</label>
            <input id="f-country" name="country" type="text" placeholder="e.g. USA, Central time">
          </div>
          <div class="field">
            <label for="f-seats">Seats you think you need</label>
            <select id="f-seats" name="seats">
              <option>Not sure yet</option>
              <option>2 &ndash; 3</option>
              <option>4 &ndash; 6</option>
              <option>7 &ndash; 12</option>
              <option>More than 12</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="f-service">Which desk interests you?</label>
          <select id="f-service" name="service">
            <option>Not sure &mdash; help me choose</option>
            {svc_opts}
            <option>Something else</option>
          </select>
        </div>

        <div class="field">
          <label for="f-message">What is stretched right now?</label>
          <textarea id="f-message" name="message" placeholder="The process, roughly how many hours a week it takes, and which systems you run it in."></textarea>
        </div>

        <button class="btn btn-primary" type="submit" style="width:100%">Send enquiry</button>
        <p class="form-note" id="form-status" hidden role="status"></p>
        <p class="form-note">This form opens your email app with the details filled in, so nothing is stored on this website. Prefer to write directly? Email <a href="mailto:{CFG['email']}">{CFG['email']}</a>.</p>
      </form>
    </div>

    <div data-reveal="right">
      <h2 style="font-size:var(--t-2)">Or reach us directly</h2>
      <div class="contact-tile">
        <span class="card-icon">{icon('mail', size=22)}</span>
        <span><b>Email</b><a href="mailto:{CFG['email']}">{CFG['email']}</a><br><span class="small">We reply within one business day</span></span>
      </div>
      <div class="contact-tile">
        <span class="card-icon">{icon('phone', size=22)}</span>
        <span><b>Phone and WhatsApp</b><a href="tel:{CFG['phone_link']}">{CFG['phone_display']}</a><br><span class="small">Also on <a href="https://wa.me/{CFG['whatsapp']}" rel="noopener">WhatsApp</a></span></span>
      </div>
      <div class="contact-tile">
        <span class="card-icon">{icon('pin', size=22)}</span>
        <span><b>Delivery centre</b><span class="small">{CFG['company']}<br>{CFG['address']}</span></span>
      </div>
      <div class="contact-tile">
        <span class="card-icon">{icon('clock', size=22)}</span>
        <span><b>When we are reachable</b><span class="small">Office hours 09:00&ndash;18:00 Nepal time for commercial conversations. Client desks run continuously, including nights and scheduled weekends.</span></span>
      </div>

      <div class="card mt-m">
        <h3>What to include for a faster answer</h3>
        <ul class="checks">
          <li>The process you would hand over first</li>
          <li>Roughly how many hours a week it takes today</li>
          <li>Which TMS or systems you run it in</li>
          <li>Your time zone and the hours you need covered</li>
          <li>When you would want to start</li>
        </ul>
        <p class="small mt-m">With those five things we can usually come back with a seat count, a monthly number and a proposed pilot in our first reply.</p>
      </div>
    </div>
  </div>
</section>

<section class="on-paper">
  <div class="wrap">
    <div style="max-width:58ch" data-reveal>
      <p class="eyebrow">Before you write</p>
      <h2>Quick answers</h2>
    </div>
    <div class="mt-l" data-reveal>
      {faq_block([
        ("How quickly can a desk start?", "Typically three to four weeks from first call: about a week to scope and write the SOP, and two to three weeks to assign and train the team on your process. If we already have trained staff available for your desk type, it can be faster."),
        ("Do you sign NDAs before scoping?", "Yes, always, and before you send us anything about your operation."),
        ("Can we visit the centre?", "Yes, and we would be pleased if you did. We also run video walkthroughs of the floor for clients who cannot travel to Pokhara."),
        ("Are you hiring? I want to apply.", f'Yes &mdash; see our <a href="careers.html">careers page</a>. Please apply through KamShip AI rather than this form so your application reaches the right team.'),
      ])}
    </div>
  </div>
</section>
"""
    schema = {
        "@context": "https://schema.org", "@type": "ContactPage",
        "name": "Contact Kamship Global",
        "mainEntity": {"@type": "Organization", "name": CFG["brand"], "email": CFG["email"],
                       "telephone": CFG["phone_display"], "url": CFG["domain"]},
    }
    page("contact.html", f"Contact | {CFG['brand']}",
         f"Email {CFG['email']} or call {CFG['phone_display']}. Tell us which freight process is stretched and we will come back with a seat count and a proposed pilot.",
         body, schema)


# ================================================================= LEGAL ===
def build_legal():
    body = page_head([("index.html", "Home")], "Privacy notice",
                     "How Kamship Global handles personal information collected through this website.", 91)
    body += f"""
<section><div class="wrap-narrow prose" data-reveal="fade">
<p class="small"><b>Status:</b> this notice is a template prepared for review by Kamship's legal adviser before publication. Last updated September 2026.</p>
<h2>Who we are</h2>
<p>This website is operated by {CFG['company']} ("Kamship", "we", "us"), {CFG['address']}. For any question about this notice, contact <a href="mailto:{CFG['email']}">{CFG['email']}</a> or call {CFG['phone_display']}.</p>
<h2>What we collect</h2>
<ul>
<li><b>Information you send us:</b> your name, work email, phone number, company, country and whatever you write in an enquiry, whether sent through the form on this site, by email or by phone.</li>
<li><b>Technical information:</b> basic request data recorded by our hosting provider, such as IP address, browser type and the pages requested, kept for security and troubleshooting.</li>
</ul>
<p>The enquiry form on this website does not transmit anything to us by itself; it opens your own email application with the message prepared, and you decide whether to send it.</p>
<h2>How we use it</h2>
<ul>
<li>To reply to your enquiry and arrange calls</li>
<li>To prepare proposals and, if you become a client, to administer the engagement</li>
<li>To keep this website secure and working</li>
</ul>
<p>We do not sell personal information, and we do not use enquiry details for unrelated marketing.</p>
<h2>Legal basis for UK and EU visitors</h2>
<p>We process enquiry information on the basis of our legitimate interest in responding to business enquiries, or on the basis of your consent where consent is required. You can withdraw consent or object at any time by emailing us.</p>
<h2>International transfers</h2>
<p>We are established in Nepal. Where personal data from the United Kingdom or the European Economic Area is transferred to us, we rely on appropriate safeguards, including the UK International Data Transfer Addendum or the EU Standard Contractual Clauses, which we enter into with clients before processing their data.</p>
<h2>Retention</h2>
<p>Enquiries are kept for up to 24 months unless a business relationship follows, in which case records are kept for the life of the engagement and for the period required by Nepali law afterwards.</p>
<h2>Your rights</h2>
<p>Subject to applicable law, you may ask us to give you access to the personal information we hold about you, correct it, delete it, restrict how we use it, or provide it in a portable form. You may also object to our use of it. Email <a href="mailto:{CFG['email']}">{CFG['email']}</a> and we will respond within 30 days.</p>
<h2>Data we process for clients</h2>
<p>When we carry out work for a client, any personal data in that client's systems is processed under our agreement with that client, who is the controller. This notice does not govern that processing; the client's own privacy notice does.</p>
<h2>Cookies</h2>
<p>This website sets no advertising or tracking cookies. If we add privacy-friendly analytics, this section will be updated to say which service and what it records before it goes live.</p>
<h2>Changes</h2>
<p>If we change this notice we will update the date above and, for material changes affecting existing enquiries or clients, tell those people directly.</p>
</div></section>
"""
    page("privacy.html", f"Privacy notice | {CFG['brand']}",
         "How Kamship Global handles personal information collected through this website.", body)

    body = page_head([("index.html", "Home")], "Website terms",
                     "The terms on which this website is made available. Draft for legal review.", 93)
    body += f"""
<section><div class="wrap-narrow prose" data-reveal="fade">
<p class="small"><b>Status:</b> template prepared for review by Kamship's legal adviser before publication.</p>
<h2>About these terms</h2>
<p>This website is published by {CFG['company']}, {CFG['address']}. By using it you accept these terms.</p>
<h2>Nothing here is an offer or a contract</h2>
<p>Descriptions of our services, targets and prices are general information to help you decide whether to talk to us. They are not an offer capable of acceptance and they do not form a contract. Services are provided only under a separately signed agreement, and where that agreement differs from this website, the agreement governs.</p>
<h2>Prices</h2>
<p>Prices shown are in US dollars, are indicative and may change. The price stated in your signed agreement is the price that applies to you.</p>
<h2>Targets and standards</h2>
<p>Performance targets described on this website are the standards we commit to in engagements. Specific targets for your engagement are agreed in writing during scoping, and those agreed targets are the ones that apply.</p>
<h2>Not professional advice</h2>
<p>Content on this website, including articles about legal developments affecting freight brokers, is general commentary and is not legal, tax, customs or financial advice. Kamship staff are not licensed customs brokers, lawyers or accountants. Take your own professional advice before acting.</p>
<h2>Third-party names</h2>
<p>Software and platform names appearing on this website are the property of their respective owners. Their use describes systems our teams are trained to work in and does not imply partnership, endorsement or certification by those owners.</p>
<h2>External links</h2>
<p>We are not responsible for the content of websites we link to.</p>
<h2>Intellectual property</h2>
<p>The content and design of this website belong to {CFG['company']}. You may read, print and share it for your own business purposes, but not republish it as your own.</p>
<h2>Liability</h2>
<p>We take care to keep this website accurate but do not guarantee it is free from error or always available. To the extent permitted by law, we are not liable for loss arising from reliance on website content. Nothing here limits liability that cannot lawfully be limited.</p>
<h2>Governing law</h2>
<p>These terms are governed by the laws of Nepal. Client agreements may specify a different governing law, agreed with each client.</p>
<h2>Contact</h2>
<p><a href="mailto:{CFG['email']}">{CFG['email']}</a> &middot; {CFG['phone_display']}</p>
</div></section>
"""
    page("terms.html", f"Terms | {CFG['brand']}", "The terms on which the Kamship Global website is made available, including pricing, targets and third-party software names.", body)

    body = """
<section class="page-head on-dark" style="min-height:62vh;display:flex;align-items:center">
""" + hero_bg(95) + f"""
  <div class="wrap" style="text-align:center">
    <p class="eyebrow" style="justify-content:center">Error 404</p>
    <h1 style="margin-inline:auto">This page has been re-routed.</h1>
    <p class="lead" style="margin-inline:auto">The link may be out of date or mistyped. Try the home page, or tell us what you were looking for.</p>
    <div class="actions" style="justify-content:center">
      <a class="btn btn-amber" href="index.html">Go to the home page</a>
      <a class="btn btn-ghost" href="contact.html">Contact us</a>
    </div>
  </div>
</section>
<section>
  <div class="wrap">
    <div style="max-width:58ch"><p class="eyebrow">Popular pages</p><h2>You might want one of these</h2></div>
    <div class="grid g-3 mt-l">
      <article class="card svc"><h3>Services</h3><p>Eight freight desks you can staff, each with written targets.</p><a class="arrow-link" href="services.html">See services {ARROW}</a></article>
      <article class="card svc"><h3>Pricing</h3><p>Published hourly rates from $7, with everything included.</p><a class="arrow-link" href="pricing.html">See pricing {ARROW}</a></article>
      <article class="card svc"><h3>Careers</h3><p>Freight roles in Pokhara with paid training.</p><a class="arrow-link" href="careers.html">See roles {ARROW}</a></article>
    </div>
  </div>
</section>
"""
    page("404.html", f"Page not found | {CFG['brand']}", "That page could not be found. Browse Kamship Global's freight back-office services, pricing and careers instead.", body,
         extra='<meta name="robots" content="noindex">')


# ============================================================ SITE FILES ===
def build_meta():
    pages = ["", "services.html"] + [f"service-{s['slug']}.html" for s in SERVICES] + [
        "industries.html", "about.html", "why-nepal.html", "security.html", "pilot.html",
        "pricing.html", "careers.html", "insights.html"] + [
        f"insight-{p['slug']}.html" for p in INSIGHTS] + ["contact.html", "privacy.html", "terms.html"]

    prio = {"": "1.0", "services.html": "0.9", "pricing.html": "0.9", "contact.html": "0.9"}
    rows = "".join(
        f'  <url><loc>{CFG["domain"]}/{p}</loc><lastmod>2026-09-24</lastmod>'
        f'<changefreq>monthly</changefreq><priority>{prio.get(p, "0.7")}</priority></url>\n'
        for p in pages)
    with open(os.path.join(SITE, "sitemap.xml"), "w", encoding="utf-8") as f:
        f.write('<?xml version="1.0" encoding="UTF-8"?>\n'
                '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' + rows + "</urlset>\n")

    with open(os.path.join(SITE, "robots.txt"), "w", encoding="utf-8") as f:
        f.write(f"User-agent: *\nAllow: /\n\nSitemap: {CFG['domain']}/sitemap.xml\n")

    with open(os.path.join(SITE, "site.webmanifest"), "w", encoding="utf-8") as f:
        json.dump({
            "name": CFG["brand"], "short_name": "Kamship",
            "description": "Supervised freight back-office teams from Pokhara, Nepal.",
            "icons": [
                {"src": "assets/img/kamship-icon-192.png", "sizes": "192x192", "type": "image/png"},
                {"src": "assets/img/kamship-icon-512.png", "sizes": "512x512", "type": "image/png"},
            ],
            "theme_color": "#0F172A", "background_color": "#FFFFFF",
            "display": "browser", "start_url": "./",
        }, f, indent=1)

    # Apache config for cPanel hosting: 404 page, compression, caching, HTTPS
    with open(os.path.join(SITE, ".htaccess"), "w", encoding="utf-8") as f:
        f.write("""# Kamship Global — Apache configuration for cPanel hosting
ErrorDocument 404 /404.html

# Force HTTPS
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/css text/plain text/xml
  AddOutputFilterByType DEFLATE application/javascript application/json
  AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>

# Caching
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType image/png "access plus 6 months"
  ExpiresByType image/svg+xml "access plus 6 months"
  ExpiresByType text/html "access plus 1 hour"
</IfModule>

# Security headers
<IfModule mod_headers.c>
  Header set X-Content-Type-Options "nosniff"
  Header set X-Frame-Options "SAMEORIGIN"
  Header set Referrer-Policy "strict-origin-when-cross-origin"
  Header set Permissions-Policy "geolocation=(), microphone=(), camera=()"
</IfModule>
""")


# ==================================================================== RUN ==
if __name__ == "__main__":
    build_home()
    build_services()
    build_service_pages()
    build_industries()
    build_about()
    build_why_nepal()
    build_security()
    build_pilot()
    build_pricing()
    build_careers()
    build_insights()
    build_insight_posts()
    build_contact()
    build_legal()
    build_meta()
    n = len([f for f in os.listdir(SITE) if f.endswith(".html")])
    print(f"Built {n} HTML pages + sitemap, robots, manifest, .htaccess")
